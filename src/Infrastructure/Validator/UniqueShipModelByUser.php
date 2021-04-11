<?php

namespace App\Infrastructure\Validator;

use App\Domain\ShipId;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniqueShipModelByUser extends Constraint
{
    /**
     * The ship that should be ignored.
     */
    public ?ShipId $excludeShipId;
    public string $message;

    public function __construct(?ShipId $excludeShipId = null, ?string $message = null, $options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);
        $this->excludeShipId = $excludeShipId;
        $this->message = $message ?? 'You have already a ship with this model.';
    }
}
