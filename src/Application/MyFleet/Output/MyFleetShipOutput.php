<?php

namespace App\Application\MyFleet\Output;

use App\Domain\ShipId;
use App\Domain\ShipTemplateId;
use OpenApi\Annotations as OpenApi;

class MyFleetShipOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", format="uid", example="00000000-0000-0000-0000-000000000001")
         */
        public ShipId $id,
        /**
         * @OpenApi\Property(type="string", example="Avenger Titan")
         */
        public string $model,
        /**
         * @OpenApi\Property(type="string", format="url", nullable=true, example="https://media.robertsspaceindustries.com/fmhdkmvhi8ify/store_small.jpg")
         */
        public ?string $imageUrl,
        /**
         * @OpenApi\Property(type="integer", description="Quantity of the ship.", example=2)
         */
        public int $quantity,
        /**
         * @OpenApi\Property(type="string", format="uid", nullable=true, example="00000000-0000-0000-0000-000000000010")
         */
        public ?ShipTemplateId $templateId,
    ) {
    }
}
