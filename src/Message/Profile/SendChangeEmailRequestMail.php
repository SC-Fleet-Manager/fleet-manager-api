<?php

namespace App\Message\Profile;

use Ramsey\Uuid\UuidInterface;

class SendChangeEmailRequestMail
{
    public function __construct(
        private UuidInterface $userId
    ) {
    }

    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }
}
