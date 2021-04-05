<?php

namespace App\Domain\Exception;

use App\Domain\UserId;
use Throwable;

class NotFoundUserException extends DomainException
{
    public static bool $notFound = true;

    public function __construct(
        UserId $userId,
        string $userMessage = '',
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        $context['userId'] = $userId;
        parent::__construct(
            'not_found_user',
            $userMessage ?: 'This user does not exist.',
            $context,
            $message ?: sprintf('Unable to find user %s.', $userId),
            $code,
            $previous,
        );
    }
}
