<?php

namespace App\Application\MyFleet\Output;

use OpenApi\Annotations as OpenApi;

class IncrementQuantityShipOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="integer", example=2, description="The new quantity of the ship.")
         */
        public int $newQuantity,
    ) {
    }
}
