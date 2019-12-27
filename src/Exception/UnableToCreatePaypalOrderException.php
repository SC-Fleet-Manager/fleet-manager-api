<?php

namespace App\Exception;

use Throwable;

class UnableToCreatePaypalOrderException extends \RuntimeException
{
    public array $paypalError;

    public function __construct(array $paypalError, $message = '', $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->paypalError = $paypalError;
    }
}
