<?php

namespace App\Domain\Exception;

use App\Domain\UserId;
use Throwable;

class AlreadyExistingFleetForUserException extends DomainException
{
    public static bool $notFound = false;

    public function __construct(
        UserId $userId,
        string $userMessage = '',
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        parent::__construct(
            'already_existing_fleet',
            $userMessage ?: 'You have already a fleet.',
            $context,
            $message ?: sprintf('A fleet is already created for the user %s.', $userId),
            $code,
            $previous,
        );
    }
}
