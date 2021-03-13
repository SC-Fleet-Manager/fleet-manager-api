<?php

namespace App\Message\Registration;

use Ramsey\Uuid\UuidInterface;

class SendLostPasswordRequestMail
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
