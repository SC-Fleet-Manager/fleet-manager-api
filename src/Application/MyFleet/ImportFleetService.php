<?php

namespace App\Application\MyFleet;

use App\Application\Common\Clock;
use App\Application\MyFleet\Input\ImportFleetShip;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\MyFleet\FleetShipImport;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Domain\UserId;
use App\Entity\Fleet;

class ImportFleetService
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private EntityIdGeneratorInterface $entityIdGenerator,
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

        $fleetShipImports = [];
        foreach ($importFleetShips as $importFleetShip) {
            $fleetShipImports[] = new FleetShipImport($importFleetShip->model);
        }
        $fleet->importShips($fleetShipImports, $onlyMissing, $this->clock->now(), $this->entityIdGenerator);

        $this->fleetRepository->save($fleet);
    }
}
