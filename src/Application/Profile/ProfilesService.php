<?php

namespace App\Application\Profile;

use App\Application\Profile\Output\ProfileOutput;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;

class ProfilesService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @param UserId[] $userIds
     *
     * @return ProfileOutput[]
     */
    public function handle(array $userIds): array
    {
        $users = $this->userRepository->getByIds($userIds);

        $result = [];
        foreach ($users as $user) {
            $result[] = new ProfileOutput(
                $user->getId(),
                $user->getAuth0Username(),
                $user->getNickname(),
                $user->isSupporterVisible(),
                $user->getCoins(),
                $user->getCreatedAt(),
            );
        }

        return $result;
    }
}
