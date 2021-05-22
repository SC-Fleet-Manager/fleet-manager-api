<?php

namespace App\Domain\Event;

use App\Entity\Ship;

class UpdatedShip
{
    public function __construct(
        public string $model,
        public ?string $logoUrl,
        public int $quantity,
    ) {
    }

    public static function createFromShip(Ship $ship): self
    {
        return new self(
            $ship->getModel(),
            $ship->getImageUrl(),
            $ship->getQuantity(),
        );
    }
}
