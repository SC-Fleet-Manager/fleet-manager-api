<?php

namespace App\Domain\Event;

use App\Domain\UserId;
use App\Entity\Fleet;

class UpdatedFleetEvent
{
    public function __construct(
        public UserId $ownerId,
        /** @var UpdatedShip[] */
        public array $ships,
        public int $version,
    ) {
    }

    public static function createFromFleet(Fleet $fleet): self
    {
        $ships = [];
        foreach ($fleet->getShips() as $ship) {
            $ships[] = UpdatedShip::createFromShip($ship);
        }

        return new self(
            $fleet->getUserId(),
            $ships,
            $fleet->getVersion(),
        );
    }
}
