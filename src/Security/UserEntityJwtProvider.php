<?php

namespace App\Security;

use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;
use App\Entity\User;
use Auth0\JWTAuthBundle\Security\Auth0Service;
use Auth0\JWTAuthBundle\Security\User\JwtUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\Cache\CacheInterface;
use Webmozart\Assert\Assert;

class UserEntityJwtProvider extends JwtUserProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EntityManagerInterface $entityManager,
        private Auth0Service $auth0Service,
        private CacheInterface $cache,
        private string $env,
        private string $supportersFilepath,
    ) {
    }

    public function loadUserByJWT(\stdClass $jwt): UserInterface
    {
        /** @var User|null $user */
        $user = $this->tryToCreateSupporter($jwt);

        if ($user === null) {
            /** @var User $user */
            $user = $this->loadUserByUsername($jwt->sub);
        }

        $profile = $this->cache->get('app.security.user_provider.profile.'.$user->getId(), function (CacheItemInterface $item) use ($user, $jwt) {
            try {
                $profile = $this->auth0Service->getUserProfileByA0UID($jwt->token);
                Assert::notNull($profile, 'UserProfile from Auth0 should not be null.');
            } catch (\Throwable $e) {
                $this->logger->warning('Unable to retrieve Auth0 profile : '.$e->getMessage(), ['exception' => $e, 'username' => $user->getUsername()]);
                $item->expiresAfter(30);

                return null;
            }

            $item->expiresAfter(86400);

            return $profile;
        }, 0 /* no early expiration */);

        $this->injectProfile($user, $profile);

        return $user;
    }

    private function tryToCreateSupporter(\stdClass $jwt): ?User
    {
        if (!in_array($this->env, ['beta'], true)) { // only supported on some envs
            return null;
        }

        $username = $jwt->sub;
        $user = $this->userRepository->findByAuth0Username($username);
        if ($user !== null) {
            // if already registered : pass
            return null;
        }

        $this->logger->info('Try to create supporter {username}', ['username' => $username]);

        $profile = $this->auth0Service->getUserProfileByA0UID($jwt->token);

        if (!file_exists($this->supportersFilepath) || !is_readable($this->supportersFilepath)) {
            throw new AccessDeniedException(sprintf('Supporters file %s does not exist or is not readable.', $this->supportersFilepath));
        }
        $supportersData = json_decode(file_get_contents($this->supportersFilepath), true);

        $totalAmount = 0;
        $nickname = null;
        $discordId = ltrim($username, 'oauth2|discord|');
        foreach ($supportersData as $supporterData) {
            if ($supporterData['discord_id'] === $discordId) {
                $totalAmount += $supporterData['amount'];
                $nickname = $supporterData['nickname'] ?? null;
                continue;
            }
            if ($profile['email_verified'] && $supporterData['email'] === $profile['email']) {
                $totalAmount += $supporterData['amount'];
                $nickname = $supporterData['nickname'] ?? null;
                continue;
            }
        }
        if ($totalAmount === 0) {
            $this->logger->error('The user {username} was not a supporters and cannot access to beta.', ['username' => $username, 'email' => $profile['email']]);
            throw new AccessDeniedException(sprintf('The user %s was not a supporters and cannot access to beta.', $username));
        }

        $user = new User(new UserId(new Ulid()), $username, $nickname, new \DateTimeImmutable($supporterData['created_at'] ?? 'now'));
        $user->setCoins($totalAmount);

        $this->logger->info('Supporter {username} created.', ['username' => $username]);

        $this->userRepository->save($user);
        $user = $this->userRepository->findByAuth0Username($username); // refresh

        return $user;
    }

    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->userRepository->findByAuth0Username($username);
        if ($user === null) {
            $user = new User(new UserId(new Ulid()), $username, null, new \DateTimeImmutable('now'));

            $this->userRepository->save($user);
            $user = $this->userRepository->findByAuth0Username($username); // refresh
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        /** @var User $user */
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getAuth0Username());
    }

    public function supportsClass($class): bool
    {
        return User::class === $class;
    }

    private function injectProfile(User $user, array $profile): void
    {
        $nickname = $profile['name'] ?? $profile['nickname'] ?? null;
        if ($nickname !== null && ($profile['email'] ?? null) === $nickname) {
            $nickname = explode('@', $nickname)[0];
        }
        $user->provideProfile(
            nickname: $nickname,
            pictureUrl: $profile['picture'] ?? null,
            locale: $profile['locale'] ?? null,
            email: $profile['email'] ?? null,
        );
    }
}
