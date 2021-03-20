<?php

namespace App\Application\Profile;

use App\Application\Exception\NotFoundUserException;
use App\Application\Profile\Output\ProfileOutput;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;

class ProfileService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handle(UserId $userId): ProfileOutput
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            throw new NotFoundUserException($userId);
        }

        return new ProfileOutput(
            $userId,
            $user->getAuth0Username(),
            $user->isSupporterVisible(),
            $user->getCoins(),
            $user->getCreatedAt(),
        );
    }
}
