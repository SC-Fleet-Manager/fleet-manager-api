<?php

namespace App\Application\MyFleet\Output;

use App\Domain\ShipId;

class MyFleetShipOutput
{
    public function __construct(
        public ShipId $id,
        public string $name,
        public ?string $imageUrl,
        public int $quantity,
    ) {
    }
}
