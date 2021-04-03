<?php

namespace App\Security;

use App\Domain\UserId;
use App\Entity\User;
use App\Repository\UserRepository;
use Auth0\JWTAuthBundle\Security\Auth0Service;
use Auth0\JWTAuthBundle\Security\User\JwtUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

class UserEntityJwtProvider extends JwtUserProvider
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private Auth0Service $auth0Service
    ) {
    }

    public function loadUserByJWT(\stdClass $jwt): UserInterface
    {
        return $this->loadUserByUsername($jwt->sub);
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
}
