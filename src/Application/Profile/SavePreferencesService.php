<?php

namespace App\Application\Profile;

use App\Application\Exception\NotFoundUserException;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;

class SavePreferencesService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(UserId $userId, bool $supporterVisible): void
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            throw new NotFoundUserException($userId);
        }

        $user->setSupporterVisible($supporterVisible);

        $this->userRepository->save($user);
    }
}
