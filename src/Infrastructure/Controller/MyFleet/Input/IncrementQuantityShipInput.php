<?php

namespace App\Infrastructure\Controller\MyFleet\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\NotIdenticalTo;

class IncrementQuantityShipInput
{
    /**
     * @OpenApi\Property(type="integer", nullable=true, not=0, example=1, default=1, description="Positive or negative delta for the quantity of ship.")
     */
    #[NotIdenticalTo(value: 0)]
    public ?int $step = null;
}
