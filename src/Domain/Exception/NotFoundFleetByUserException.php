<?php

namespace App\Domain\Exception;

use App\Domain\UserId;
use Throwable;

class NotFoundFleetByUserException extends DomainException
{
    public static bool $notFound = true;

    public function __construct(
        UserId $userId,
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        $context['userId'] = $userId;
        parent::__construct(
            'not_found_fleet',
            'This user has no fleet. Please try to create a ship.',
            $context,
            $message ?: sprintf('Unable to find fleet of user %s.', $userId),
            $code,
            $previous,
        );
    }
}
