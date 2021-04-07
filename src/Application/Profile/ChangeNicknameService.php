<?php

namespace App\Application\Profile;

use App\Application\Repository\UserRepositoryInterface;
use App\Domain\Exception\NotFoundUserException;
use App\Domain\UserId;

class ChangeNicknameService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(UserId $userId, ?string $nickname): void
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            throw new NotFoundUserException($userId);
        }

        $user->changeNickname($nickname);

        $this->userRepository->save($user);
    }
}
