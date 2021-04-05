<?php

namespace App\Infrastructure\Controller\MyFleet\Input;

use Symfony\Component\Validator\Constraints\NotIdenticalTo;

class IncrementQuantityShipInput
{
    #[NotIdenticalTo(value: 0)]
    public ?int $step = null;
}
