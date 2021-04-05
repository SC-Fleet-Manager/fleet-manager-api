<?php

namespace App\Domain\Exception;

use Throwable;

class ConflictVersionException extends DomainException
{
    public static bool $notFound = false;

    public function __construct(
        object $entity,
        string $userMessage = '',
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        parent::__construct(
            'conflict_version',
            $userMessage ?: 'Unable to update. Please, try again.',
            $context,
            $message ?: sprintf('The entity %s could not be saved : %s', $entity::class, $previous?->getMessage() ?? ''),
            $code,
            $previous,
        );
    }
}
