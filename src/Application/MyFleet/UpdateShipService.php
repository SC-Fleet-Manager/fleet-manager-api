<?php

namespace App\Application\MyFleet;

use App\Application\Common\Clock;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Exception\ConflictVersionException;
use App\Domain\Exception\NotFoundFleetByUserException;
use App\Domain\Exception\NotFoundShipException;
use App\Domain\ShipId;
use App\Domain\UserId;

class UpdateShipService
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
    public function handle(UserId $userId, ShipId $shipId, string $name, ?string $imageUrl, int $quantity): void
    {
        $fleet = $this->fleetRepository->getFleetByUser($userId);
        if ($fleet === null) {
            throw new NotFoundFleetByUserException($userId);
        }

        $fleet->updateShip($shipId, $name, $imageUrl, $quantity, $this->clock->now());

        $this->fleetRepository->save($fleet);
    }
}
