<?php

namespace App\Event;

use App\Entity\Citizen;
use App\Entity\Fleet;
use Symfony\Contracts\EventDispatcher\Event;

class CitizenFleetUpdatedEvent extends Event
{
    private Citizen $citizen;
    private Fleet $newFleet;
    private ?Fleet $oldFleet;

    public function __construct(Citizen $citizen, Fleet $newFleet, ?Fleet $oldFleet = null)
    {
        $this->citizen = $citizen;
        $this->newFleet = $newFleet;
        $this->oldFleet = $oldFleet;
    }

    public function getCitizen(): Citizen
    {
        return $this->citizen;
    }

    public function getNewFleet(): Fleet
    {
        return $this->newFleet;
    }

    public function getOldFleet(): ?Fleet
    {
        return $this->oldFleet;
    }
}
