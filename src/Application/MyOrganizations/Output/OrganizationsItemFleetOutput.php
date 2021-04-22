<?php

namespace App\Application\MyOrganizations\Output;

use App\Entity\OrganizationFleet;

class OrganizationsItemFleetOutput
{
    public function __construct(
        /**
         * @var OrganizationsItemFleetShipsOutput[]
         */
        public array $ships,
    ) {
    }

    public static function createFromFleet(OrganizationFleet $fleet): self
    {
        $ships = [];
        foreach ($fleet->getShips() as $ship) {
            $ships[] = OrganizationsItemFleetShipsOutput::createFromShip($ship);
        }

        return new self($ships);
    }
}
