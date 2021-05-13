<?php

namespace App\Application\ShipTemplate\Output;

use App\Entity\CargoCapacity;
use OpenApi\Annotations as OpenApi;

class CargoCapacityOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="integer")
         */
        public int $capacity = 0,
    ) {
    }

    public static function fromEntity(CargoCapacity $cargoCapacity): self
    {
        return new self(
            $cargoCapacity->getCapacity(),
        );
    }
}
