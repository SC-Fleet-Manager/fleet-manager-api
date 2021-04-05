<?php

namespace App\Application\MyFleet\Output;

class IncrementQuantityShipOutput
{
    public function __construct(
        public int $newQuantity,
    ) {
    }
}
