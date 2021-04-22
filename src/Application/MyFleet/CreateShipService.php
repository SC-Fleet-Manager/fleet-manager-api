<?php

namespace App\Application\MyFleet;

use App\Application\Common\Clock;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;

class CreateShipService
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private Clock $clock,
    ) {
    }

    public function handle(UserId $userId, ShipId $shipId, string $model, ?string $imageUrl, ?int $quantity = null): void
    {
        $fleet = $this->fleetRepository->getFleetByUser($userId);
        if ($fleet === null) {
            $fleet = new Fleet($userId, $this->clock->now());
        }

        $fleet->addShip($shipId, $model, $imageUrl, $quantity ?? 1, $this->clock->now());

        $this->fleetRepository->save($fleet);
    }
}
