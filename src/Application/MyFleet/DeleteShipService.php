<?php

namespace App\Application\MyFleet;

use App\Application\Common\Clock;
use App\Domain\Exception\ConflictVersionException;
use App\Domain\Exception\NotFoundFleetByUserException;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\ShipId;
use App\Domain\UserId;
use Psr\Log\LoggerInterface;

class DeleteShipService
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private Clock $clock,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws NotFoundFleetByUserException
     * @throws ConflictVersionException
     */
    public function handle(UserId $userId, ShipId $shipId): void
    {
        $fleet = $this->fleetRepository->getFleetByUser($userId);
        if ($fleet === null) {
            throw new NotFoundFleetByUserException($userId);
        }

        $fleet->deleteShip($shipId, $this->clock);

        $this->fleetRepository->save($fleet);
    }
}
