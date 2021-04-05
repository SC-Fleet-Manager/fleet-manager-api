<?php

namespace App\Domain\Exception;

use Throwable;

abstract class DomainException extends \RuntimeException
{
    public static bool $notFound = false;

    public string $error;
    public string $userMessage;
    public array $context;

    public function __construct(string $error, string $userMessage, array $context = [], $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->error = $error;
        $this->userMessage = $userMessage;
        $this->context = $context;
    }
}
