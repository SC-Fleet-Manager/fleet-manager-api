<?php

namespace App\Domain\MyFleet;

class FleetShipImport
{
    public function __construct(
        public string $model,
    ) {
    }
}
