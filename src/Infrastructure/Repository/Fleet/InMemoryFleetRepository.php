<?php

namespace App\Infrastructure\Repository\Fleet;

use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\UserId;
use App\Entity\Fleet;
use Symfony\Component\Messenger\MessageBusInterface;

class InMemoryFleetRepository implements FleetRepositoryInterface
{
    /** @var Fleet[] */
    private array $fleets = [];

    public function __construct(
        private MessageBusInterface $eventBus
    ) {
    }

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

        foreach ($fleet->getAndClearEvents() as $event) {
            $this->eventBus->dispatch($event);
        }
    }

    public function delete(Fleet $fleet): void
    {
        unset($this->fleets[(string) $fleet->getUserId()]);
    }
}
