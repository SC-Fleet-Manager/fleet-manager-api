<?php

namespace App\Application\Support;

use App\Application\Repository\UserRepositoryInterface;
use App\Domain\Exception\NotFoundUserException;
use App\Domain\Notification\FeedbackNotification;
use App\Domain\UserId;
use App\Domain\UserProfile;
use Symfony\Component\Notifier\NotifierInterface;

class GiveFeedbackService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private NotifierInterface $notifier,
    ) {
    }

    public function handle(UserId $userId, UserProfile $profile, string $description, ?string $email, ?string $discordId): void
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            throw new NotFoundUserException($userId);
        }

        $this->notifier->send(new FeedbackNotification(
            $description,
            $user->getNickname(),
            $user->getCoins(),
            $profile->getEmail(),
            $profile->getDiscordId(),
            $user->getCreatedAt(),
            $email,
            $discordId,
        ));
    }
}
