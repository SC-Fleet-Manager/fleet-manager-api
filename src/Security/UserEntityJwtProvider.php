<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Auth0\JWTAuthBundle\Security\Auth0Service;
use Auth0\JWTAuthBundle\Security\User\JwtUserProvider;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserEntityJwtProvider extends JwtUserProvider
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private Auth0Service $auth0Service
    ) {
    }

    public function loadUserByUsername($username): UserInterface
    {
        $user = $this->userRepository->findByAuth0Username($username);
        if ($user === null) {
            $user = new User(Uuid::uuid4());
            $user->setAuth0Username($username);
            $user->setToken(User::generateToken());
            $user->setApiToken(User::generateToken());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
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
