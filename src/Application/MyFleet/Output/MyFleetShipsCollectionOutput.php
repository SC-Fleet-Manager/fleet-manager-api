<?php

namespace App\Application\MyFleet\Output;

class MyFleetShipsCollectionOutput
{
    public function __construct(
        public array $items,
        public int $count,
    ) {
    }
}
