<?php

namespace App\Application\Exception;

use App\Domain\UserId;
use Throwable;

class AlreadyExistingFleetForUserException extends \RuntimeException
{
    public UserId $userId;

    public function __construct(UserId $userId, $message = '', $code = 0, Throwable $previous = null)
    {
        $message = $message ?: sprintf('A fleet is already created for the user %s.', $userId);
        parent::__construct($message, $code, $previous);
        $this->userId = $userId;
    }
}
