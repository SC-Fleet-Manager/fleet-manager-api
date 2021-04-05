<?php

namespace App\Application\MyFleet;

use App\Application\Common\Clock;
use App\Application\Exception\ConflictVersionException;
use App\Application\Exception\NotFoundFleetByUserException;
use App\Application\MyFleet\Output\IncrementQuantityShipOutput;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Exception\NotFoundShipException;
use App\Domain\ShipId;
use App\Domain\UserId;

class IncrementQuantityShipService
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private Clock $clock,
    ) {
    }

    /**
     * @throws NotFoundFleetByUserException
     * @throws NotFoundShipException
     * @throws ConflictVersionException
     */
    public function handle(UserId $userId, ShipId $shipId, int $step): IncrementQuantityShipOutput
    {
        $fleet = $this->fleetRepository->getFleetByUser($userId);
        if ($fleet === null) {
            throw new NotFoundFleetByUserException($userId);
        }

        $newQuantity = $fleet->incrementShipQuantity($shipId, $step, $this->clock);

        $this->fleetRepository->save($fleet);

        return new IncrementQuantityShipOutput($newQuantity);
    }
}
