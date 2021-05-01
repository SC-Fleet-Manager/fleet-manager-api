<?php

namespace App\Application\MyFleet\Input;

class ImportFleetShip
{
    public function __construct(
        public string $model,
    ) {
    }
}
