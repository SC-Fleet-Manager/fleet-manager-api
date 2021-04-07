<?php

namespace App\Domain\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Throwable;

class FailedValidationException extends DomainException
{
    public function __construct(
        ConstraintViolationListInterface $violations,
        string $userMessage = '',
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        $context['violations'] = $violations;
        parent::__construct(
            'invalid_form',
            $userMessage ?: 'The form is invalid.',
            $context,
            $message ?: 'Invalid form.',
            $code,
            $previous,
        );
    }
}
