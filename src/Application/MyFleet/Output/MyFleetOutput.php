<?php

namespace App\Application\MyFleet\Output;

use OpenApi\Annotations as OpenApi;

class MyFleetOutput
{
    public function __construct(
        public MyFleetShipsCollectionOutput $ships,
        /**
         * @OpenApi\Property(type="string", format="date-time")
         */
        public \DateTimeInterface $updatedAt,
    ) {
    }
}
