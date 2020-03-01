<?php

namespace App\Security;

use App\Entity\User;
use App\Security\Exception\NotConfirmedRegistrationException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ConfirmedUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getEmail() !== null && !$user->isEmailConfirmed()) {
            throw new NotConfirmedRegistrationException($user->getId(), $user->getUserEmail(), 'You have not confirmed your registration yet. Please check your emails.');
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
    }
}
