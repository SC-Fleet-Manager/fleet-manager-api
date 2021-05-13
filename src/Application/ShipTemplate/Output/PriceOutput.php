<?php

namespace App\Application\ShipTemplate\Output;

use App\Entity\Price;
use OpenApi\Annotations as OpenApi;

class PriceOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="integer", format="money", nullable=true, description="In USD cents.")
         */
        public ?int $pledge = null,
        /**
         * @OpenApi\Property(type="integer", format="money", nullable=true, description="In UEC.")
         */
        public ?int $ingame = null,
    ) {
    }

    public static function fromEntity(Price $price): self
    {
        return new self(
            $price->getPledge() !== null ? $price->getPledge()->getAmount() : null,
            $price->getIngame() !== null ? $price->getIngame()->getAmount() : null,
        );
    }
}
