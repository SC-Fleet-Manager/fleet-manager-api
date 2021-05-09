<?php

namespace App\Infrastructure\Controller\ShipTemplate\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\Expression;
use Symfony\Component\Validator\Constraints\Range;

#[Expression('this.min == null or this.max == null or this.min <= this.max', message: 'Max crew must be greater than or equal to min.')]
class CreateTemplateCrewInput
{
    /**
     * @OpenApi\Property(type="integer", nullable=true)
     */
    #[range(min: 1, max: 500)]
    public mixed $min;

    /**
     * @OpenApi\Property(type="integer", nullable=true)
     */
    #[range(min: 1, max: 500)]
    public mixed $max;
}
