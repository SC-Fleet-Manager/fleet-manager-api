<?php

namespace App\Application\Exception;

use App\Domain\UserId;
use Throwable;

class NotFoundUserException extends \RuntimeException
{
    public UserId $userId;

    public function __construct(UserId $userId, string $message = '', int $code = 0, ?Throwable $previous = null)
    {
        $message = $message ?: sprintf('Unable to find user %s.', $userId);
        parent::__construct($message, $code, $previous);
        $this->userId = $userId;
    }
}
