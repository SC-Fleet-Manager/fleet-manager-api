<?php

namespace App\Infrastructure\Repository\Fleet;

use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\UserId;
use App\Entity\Fleet;

class InMemoryFleetRepository implements FleetRepositoryInterface
{
    /** @var Fleet[] */
    private array $fleetByUser = [];

    /**
     * @param Fleet[] $fleets
     */
    public function setFleets(array $fleets): void
    {
        $this->fleetByUser = [];
        foreach ($fleets as $fleet) {
            $this->fleetByUser[(string) $fleet->getUserId()] = $fleet;
        }
    }

    public function getFleetByUser(UserId $userId): ?Fleet
    {
        return $this->fleetByUser[(string) $userId] ?? null;
    }
}
