<?php

namespace App\Application\ShipTemplate\Output;

use App\Entity\ShipChassis;
use OpenApi\Annotations as OpenApi;

class ShipChassisOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", nullable=false, example="Aurora")
         */
        public string $name,
    ) {
    }

    public static function fromEntity(ShipChassis $chassis): self
    {
        return new self(
            $chassis->getName(),
        );
    }
}
