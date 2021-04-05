<?php

namespace App\Application\MyFleet\Output;

use OpenApi\Annotations as OpenApi;

class MyFleetShipsCollectionOutput
{
    public function __construct(
        /**
         * @var MyFleetShipOutput[]
         */
        public array $items,
        /**
         * @OpenApi\Property(type="integer", description="Number of different ships.", example=5)
         */
        public int $count,
    ) {
    }
}
