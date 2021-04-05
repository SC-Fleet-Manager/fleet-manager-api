<?php

namespace App\Application\Exception;

use Throwable;

class ConflictVersionException extends \RuntimeException
{
    public object $entity;

    public function __construct(object $entity, $message = '', $code = 0, Throwable $previous = null)
    {
        $message = $message ?: sprintf('The entity %s could not be saved : %s', $entity::class, $previous?->getMessage() ?? '');
        parent::__construct($message, $code, $previous);
        $this->entity = $entity;
    }
}
