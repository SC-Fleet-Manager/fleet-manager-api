<?php

namespace App\Application\MyFleet\Output;

use App\Domain\FleetId;

class MyFleetOutput
{
    public function __construct(
        public FleetId $id,
        public MyFleetShipsCollectionOutput $ships,
        public \DateTimeInterface $updatedAt,
    ) {
    }
}
