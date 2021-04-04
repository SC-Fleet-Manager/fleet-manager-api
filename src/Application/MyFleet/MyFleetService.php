<?php

namespace App\Application\MyFleet;

use App\Application\Exception\NotFoundFleetByUserException;
use App\Application\MyFleet\Output\MyFleetOutput;
use App\Application\MyFleet\Output\MyFleetShipOutput;
use App\Application\MyFleet\Output\MyFleetShipsCollectionOutput;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\UserId;

class MyFleetService
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository
    ) {
    }

    public function handle(UserId $userId): MyFleetOutput
    {
        $fleet = $this->fleetRepository->getFleetByUser($userId);
        if ($fleet === null) {
            throw new NotFoundFleetByUserException($userId);
        }

        $shipOutputItems = [];
        foreach ($fleet->getShips() as $ship) {
            $shipOutputItems[] = new MyFleetShipOutput($ship->getId(), $ship->getName(), $ship->getImageUrl(), $ship->getQuantity());
        }

        return new MyFleetOutput(
            id: $fleet->getId(),
            ships: new MyFleetShipsCollectionOutput(items: $shipOutputItems, count: count($shipOutputItems)),
            updatedAt: $fleet->getUpdatedAt(),
        );
    }
}
