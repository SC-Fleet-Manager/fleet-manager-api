<?php

namespace App\Domain\Notification;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\RecipientInterface;

class FeedbackNotification extends Notification
{
    public function __construct(
        string $description,
        ?string $nickname,
        int $coins,
        ?string $email,
        ?string $discordId,
        \DateTimeInterface $registeredAt,
        ?string $givenEmail,
        ?string $givenDiscordId,
    ) {
        $formattedRegisteredAt = $registeredAt->format('Y-m-d');
        parent::__construct(<<<EOT
            Email: $email
            GivenEmail: $givenEmail
            DiscordId: $discordId
            GivenDiscordId: $givenDiscordId
            Nickname: $nickname
            RegisteredAt: $formattedRegisteredAt
            Coins: $coins
            $description
            EOT
        );
    }

    public function getChannels(RecipientInterface $recipient): array
    {
        return ['chat/discord'];
    }
}
