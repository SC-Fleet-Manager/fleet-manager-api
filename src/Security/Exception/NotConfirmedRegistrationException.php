<?php

namespace App\Security\Exception;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\Exception\AccountStatusException;
use Throwable;

class NotConfirmedRegistrationException extends AccountStatusException
{
    public UuidInterface $userId;
    public string $username;

    public function __construct(UuidInterface $userId, string $username, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->userId = clone $userId;
        $this->username = $username;
    }

    public function getMessageKey(): string
    {
        return 'not_confirmed_registration';
    }
}
