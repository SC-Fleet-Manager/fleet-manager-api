<?php

namespace App\Application\Exception;

use App\Domain\UserId;
use Throwable;

class NotFoundFleetByUserException extends \RuntimeException
{
    public UserId $userId;

    public function __construct(UserId $userId, $message = '', $code = 0, Throwable $previous = null)
    {
        $message = $message ?: sprintf('Unable to find fleet of user %s.', $userId);
        parent::__construct($message, $code, $previous);
        $this->userId = $userId;
    }
}
