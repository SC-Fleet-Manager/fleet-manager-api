<?php

namespace App\Application\MyFleet;

use App\Application\Common\Clock;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Exception\ConflictVersionException;
use App\Domain\Exception\NotFoundFleetByUserException;
use App\Domain\ShipId;
use App\Domain\UserId;

class ClearMyFleetService
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private Clock $clock,
    ) {
    }

    public function handle(UserId $userId): void
    {
        $fleet = $this->fleetRepository->getFleetByUser($userId);
        if ($fleet === null) {
            return;
        }

        $fleet->deleteAllShips($this->clock->now());

        $this->fleetRepository->save($fleet);
    }
}
