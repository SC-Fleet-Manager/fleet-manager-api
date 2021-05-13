<?php

namespace App\Application\ShipTemplate\Output;

use App\Domain\ShipTemplateId;
use OpenApi\Annotations as OpenApi;
use App\Entity\ShipSize;

class ListTemplatesItemOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", format="uid", example="00000000-0000-0000-0000-000000000001")
         */
        public ShipTemplateId $id,
        /**
         * @OpenApi\Property(type="string", example="Avenger Titan")
         */
        public string $model,
        /**
         * @OpenApi\Property(type="string", format="url", nullable=true, example="https://media.robertsspaceindustries.com/fmhdkmvhi8ify/store_small.jpg")
         */
        public ?string $pictureUrl,
        public ShipChassisOutput $shipChassis,
        public ManufacturerOutput $manufacturer,
        /**
         * @OpenApi\Property(type="string", nullable=true, enum=ShipSize::SIZES)
         */
        public ?string $size,
        /**
         * @OpenApi\Property(type="string", nullable=true, example="Combat")
         */
        public ?string $role,
        public CargoCapacityOutput $cargoCapacity,
        public CrewOutput $crew,
        public PriceOutput $price,
    ) {
    }
}
