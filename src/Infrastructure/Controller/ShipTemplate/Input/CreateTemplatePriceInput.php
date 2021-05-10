<?php

namespace App\Infrastructure\Controller\ShipTemplate\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\Range;

class CreateTemplatePriceInput
{
    /**
     * @OpenApi\Property(type="integer", nullable=true, description="In USD cents.")
     */
    #[range(min: 0, max: 2_000_000_000)]
    public mixed $pledge = null;

    /**
     * @OpenApi\Property(type="integer", nullable=true, description="in UEC.")
     */
    #[range(min: 0, max: 2_000_000_000)]
    public mixed $inGame = null;
}
