<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UuidProvider implements UserProviderInterface
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    public function loadUserByUsername($uid): UserInterface
    {
        if ($uid === null) {
            throw new UsernameNotFoundException('User not found with this UUID.');
        }
        if (is_string($uid) && Uuid::isValid($uid)) {
            $uid = Uuid::fromString($uid);
        }
        if (!$uid instanceof UuidInterface) {
            throw new UsernameNotFoundException('User not found with this UUID.');
        }
        $user = $this->userRepository->find($uid);
        if ($user === null) {
            throw new UsernameNotFoundException('User not found with this UUID.');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        /** @var User $user */
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getId());
    }

    public function supportsClass($class): bool
    {
        return User::class === $class;
    }
}
