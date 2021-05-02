<?php

namespace App\Application\MyFleet\Input;

use Symfony\Component\Validator\Constraints\Length;

class ImportFleetShip
{
    public function __construct(
        #[Length(min: 2, max: 60)]
        public string $model,
    ) {
    }
}
