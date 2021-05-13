<?php

namespace App\Application\ShipTemplate\Output;

use App\Entity\Manufacturer;
use OpenApi\Annotations as OpenApi;

class ManufacturerOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", nullable=true, example="Robert Space Industries")
         */
        public ?string $name = null,
        /**
         * @OpenApi\Property(type="string", nullable=true, example="RSI")
         */
        public ?string $code = null,
    ) {
    }

    public static function fromEntity(Manufacturer $manufacturer): self
    {
        return new self(
            $manufacturer->getName(),
            $manufacturer->getCode(),
        );
    }
}
