<?php

namespace App\Application\MyOrganizations\Output;

use App\Entity\OrganizationShip;
use OpenApi\Annotations as OpenApi;

class OrganizationsItemFleetShipsOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", example="Avenger Titan")
         */
        public string $model,
        /**
         * @OpenApi\Property(type="string", format="url", nullable=true, example="https://media.robertsspaceindustries.com/fmhdkmvhi8ify/store_small.jpg")
         */
        public ?string $imageUrl,
        /**
         * @OpenApi\Property(type="integer", description="Quantity of the ship accross the organization.", example=2)
         */
        public int $quantity,
    ) {
    }

    public static function createFromShip(OrganizationShip $ship): self
    {
        return new self(
            $ship->getModel(),
            $ship->getImageUrl(),
            $ship->getQuantity(),
        );
    }
}
