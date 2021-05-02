<?php

namespace App\Application\MyFleet;

use App\Application\Common\Clock;
use App\Application\MyFleet\Input\ImportFleetShip;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use Symfony\Component\Uid\Ulid;

class ImportFleetService
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private Clock $clock,
    ) {
    }

    /**
     * @param ImportFleetShip[] $importFleetShips
     */
    public function handle(UserId $userId, array $importFleetShips, bool $onlyMissing = false): void
    {
        $fleet = $this->fleetRepository->getFleetByUser($userId);
        if ($fleet === null) {
            $fleet = new Fleet($userId, $this->clock->now());
        }

        $addedShips = [];
        foreach ($importFleetShips as $importFleetShip) {
            $ship = $fleet->getShipByModel($importFleetShip->model);
            if ($ship === null) {
                $fleet->addShip(new ShipId(new Ulid()), $importFleetShip->model, null, 1, $this->clock->now());
                $ship = $fleet->getShipByModel($importFleetShip->model);
                $addedShips[(string) $ship->getId()] = $ship;
                continue;
            }
            if (!$onlyMissing || isset($addedShips[(string) $ship->getId()])) {
                $fleet->updateShip($ship->getId(), $importFleetShip->model, $ship->getImageUrl(), 1 + $ship->getQuantity(), $this->clock->now());
            }
        }
        unset($addedShips);

        $this->fleetRepository->save($fleet);
    }
}
