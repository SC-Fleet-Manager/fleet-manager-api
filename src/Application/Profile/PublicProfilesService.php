<?php

namespace App\Application\Profile;

use App\Application\Profile\Output\ProfileOutput;
use App\Application\Profile\Output\PublicProfileOutput;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;

class PublicProfilesService
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
            $result[] = new PublicProfileOutput(
                $user->getId(),
                $user->getNickname(),
            );
        }

        return $result;
    }
}
