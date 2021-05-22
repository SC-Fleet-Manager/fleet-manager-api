<?php

namespace App\Security;

use App\Application\Repository\UserRepositoryInterface;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Domain\UserId;
use App\Entity\User;
use Auth0\JWTAuthBundle\Security\Auth0Service;
use Auth0\JWTAuthBundle\Security\User\JwtUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use function Symfony\Component\String\u;
use Symfony\Component\Uid\Ulid;
use Symfony\Contracts\Cache\CacheInterface;

class UserEntityJwtProvider extends JwtUserProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const RULE_DOMAIN = 'https://api.fleet-manager.space';

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EntityManagerInterface $entityManager,
        private EntityIdGeneratorInterface $entityIdGenerator,
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
            $user = $this->userRepository->findByAuth0Username($jwt->sub);
            if ($user === null) {
                $user = new User($this->entityIdGenerator->generateEntityId(UserId::class), $jwt->sub, $this->tryToExtractNickname($jwt), new \DateTimeImmutable('now'));
                $this->userRepository->save($user);
            }
            /** @var User $user */
            $user = $this->loadUserByUsername($jwt->sub);
        }

        $this->injectProfile($user, $jwt);

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

        $email = $jwt->{self::RULE_DOMAIN.'/email'} ?? null;
        $emailVerified = $jwt->{self::RULE_DOMAIN.'/email_verified'} ?? false;

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
            if ($emailVerified && $supporterData['email'] === $email) {
                $totalAmount += $supporterData['amount'];
                $nickname = $supporterData['nickname'] ?? null;
                continue;
            }
        }
        if ($totalAmount === 0) {
            $this->logger->notice('The user {username} was not a supporter and cannot access to beta.', ['username' => $username, 'email' => $email]);
            throw new AccessDeniedException(sprintf('The user %s was not a supporter and cannot access to beta.', $username));
        }

        $user = new User(new UserId(new Ulid()), $username, $nickname, new \DateTimeImmutable($supporterData['created_at'] ?? 'now'));
        $user->setCoins($totalAmount);

        $this->logger->info('Supporter {username} created.', ['username' => $username]);

        $this->userRepository->save($user);

        return $this->userRepository->findByAuth0Username($username); // refresh
    }

    public function loadUserByUsername($username): UserInterface
    {
        return $this->userRepository->findByAuth0Username($username);
    }

    private function tryToExtractNickname($jwt): ?string
    {
        if (!u($jwt->sub)->startsWith('oauth2|discord|')) {
            // must be Discord auth
            return null;
        }

        return $jwt->{self::RULE_DOMAIN.'/nickname'} ?? null;
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

    private function injectProfile(User $user, \stdClass $jwt): void
    {
        $email = $jwt->{self::RULE_DOMAIN.'/email'} ?? null;
        $nickname = $jwt->{self::RULE_DOMAIN.'/nickname'} ?? null;
        $name = $jwt->{self::RULE_DOMAIN.'/name'} ?? null;

        $nickname = $name ?? $nickname ?? null;
        if ($nickname !== null && $email === $nickname) {
            $nickname = explode('@', $nickname)[0];
        }
        $discordId = !u($jwt->sub)->startsWith('oauth2|discord|') ? null : u($jwt->sub)->trimStart('oauth2|discord|');

        $user->provideProfile(
            nickname: $nickname,
            email: $email,
            discordId: $discordId,
        );
    }
}
