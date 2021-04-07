<?php

namespace App\Application\Profile;

use App\Application\Repository\UserRepositoryInterface;
use App\Domain\Event\DeletedUser;
use App\Domain\UserId;
use Symfony\Component\Messenger\MessageBusInterface;

class DeleteAccountService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MessageBusInterface $bus,
    ) {
    }

    public function handle(UserId $userId): void
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            return;
        }

        $this->userRepository->delete($user);
        $this->bus->dispatch(new DeletedUser($userId, $user->getAuth0Username()));
    }
}
