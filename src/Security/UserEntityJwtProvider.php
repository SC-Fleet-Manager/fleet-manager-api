<?php

namespace App\Security;

use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;
use App\Entity\User;
use Auth0\JWTAuthBundle\Security\Auth0Service;
use Auth0\JWTAuthBundle\Security\User\JwtUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

class UserEntityJwtProvider extends JwtUserProvider implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private EntityManagerInterface $entityManager,
        private Auth0Service $auth0Service
    ) {
    }

    public function loadUserByJWT(\stdClass $jwt): UserInterface
    {
        /** @var User $user */
        $user = $this->loadUserByUsername($jwt->sub);

        try {
            $profile = $this->auth0Service->getUserProfileByA0UID($jwt->token);
            Assert::notNull($profile, 'UserProfile from Auth0 should not be null.');
            $this->injectProfile($user, $profile);
        } catch (\Throwable $e) {
            $this->logger->warning('Unable to retrieve Auth0 profile : '.$e->getMessage(), ['exception' => $e, 'username' => $user->getUsername()]);
        }

        return $user;
    }

    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->userRepository->findByAuth0Username($username);
        if ($user === null) {
            $user = new User(new UserId(new Ulid()), $username, new \DateTimeImmutable('now'));

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
