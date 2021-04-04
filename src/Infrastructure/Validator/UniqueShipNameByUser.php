<?php

namespace App\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniqueShipNameByUser extends Constraint
{
    public string $message;

    public function __construct(?string $message = null, $options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);
        $this->message = $message ?? 'You have already a ship with this name.';
    }
}
