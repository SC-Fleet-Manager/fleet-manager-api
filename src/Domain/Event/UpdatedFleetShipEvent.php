<?php

namespace App\Domain\Event;

use App\Domain\UserId;
use App\Entity\Ship;

class UpdatedFleetShipEvent
{
    public function __construct(
        public UserId $ownerId,
        public string $model,
        public ?string $logoUrl,
        public int $quantity,
    ) {
    }

    public static function createFromShip(UserId $ownerId, Ship $ship): self
    {
        return new self(
            $ownerId,
            $ship->getModel(),
            $ship->getImageUrl(),
            $ship->getQuantity(),
        );
    }
}
