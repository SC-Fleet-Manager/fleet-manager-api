<?php


namespace App\Application\Profile;


use App\Application\Repository\UserRepositoryInterface;
use App\Domain\Exception\NotFoundUserException;
use App\Domain\UserId;

class ChangeHandleService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(UserId $userId, string $handle): void
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            throw new NotFoundUserException($userId);
        }

        $user->changeHandle($handle);

        $this->userRepository->save($user);
    }
}
