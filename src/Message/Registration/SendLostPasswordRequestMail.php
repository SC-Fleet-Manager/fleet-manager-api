<?php

namespace App\Message\Registration;

use Ramsey\Uuid\UuidInterface;

class SendLostPasswordRequestMail
{
    /** @var UuidInterface */
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
