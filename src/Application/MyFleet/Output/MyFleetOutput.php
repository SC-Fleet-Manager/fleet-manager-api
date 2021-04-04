<?php

namespace App\Application\MyFleet\Output;

class MyFleetOutput
{
    public function __construct(
        public MyFleetShipsCollectionOutput $ships,
        public \DateTimeInterface $updatedAt,
    ) {
    }
}
