<?php

namespace App\Message\Registration;

use Ramsey\Uuid\UuidInterface;

class SendRegistrationConfirmationMail
{
    private $userId;

    public function __construct(UuidInterface $userId)
    {
        $this->userId = $userId;
    }

    public function getUserId(): UuidInterface
    {
        return $this->userId;
    }
}
