<?php

namespace App\Application\Home;

use App\Domain\Exception\NotFoundUserException;
use App\Application\Home\Output\MeOutput;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;

class MeService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(UserId $userId): MeOutput
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            throw new NotFoundUserException($userId);
        }

        return new MeOutput($userId, $user->getCreatedAt());
    }
}
