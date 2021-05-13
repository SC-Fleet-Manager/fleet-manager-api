<?php

namespace App\Application\ShipTemplate\Output;

use App\Entity\Crew;
use OpenApi\Annotations as OpenApi;

class CrewOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="integer", nullable=true)
         */
        public ?int $min = null,
        /**
         * @OpenApi\Property(type="integer", nullable=true)
         */
        public ?int $max = null,
    ) {
    }

    public static function fromEntity(Crew $crew): self
    {
        return new self(
            $crew->getMin(),
            $crew->getMax(),
        );
    }
}
