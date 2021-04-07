<?php

namespace App\Infrastructure\Repository\Fleet;

use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\UserId;
use App\Entity\Fleet;

class InMemoryFleetRepository implements FleetRepositoryInterface
{
    /** @var Fleet[] */
    private array $fleets = [];

    /**
     * @param Fleet[] $fleets
     */
    public function setFleets(array $fleets): void
    {
        $this->fleets = [];
        foreach ($fleets as $fleet) {
            $this->fleets[(string) $fleet->getUserId()] = $fleet;
        }
    }

    public function getFleetByUser(UserId $userId): ?Fleet
    {
        return $this->fleets[(string) $userId] ?? null;
    }

    public function save(Fleet $fleet): void
    {
        $this->fleets[(string) $fleet->getUserId()] = $fleet;
    }

    public function delete(Fleet $fleet): void
    {
        unset($this->fleets[(string) $fleet->getUserId()]);
    }
}
