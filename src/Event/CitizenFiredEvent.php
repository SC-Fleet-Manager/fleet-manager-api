<?php

namespace App\Event;

use App\Entity\Citizen;
use App\Entity\Organization;
use Symfony\Contracts\EventDispatcher\Event;

class CitizenFiredEvent extends Event
{
    private Citizen $firedCitizen;
    private Organization $firingOrga;

    public function __construct(Citizen $firedCitizen, Organization $firingOrga)
    {
        $this->firedCitizen = $firedCitizen;
        $this->firingOrga = $firingOrga;
    }

    public function getFiredCitizen(): Citizen
    {
        return $this->firedCitizen;
    }

    public function getFiringOrga(): Organization
    {
        return $this->firingOrga;
    }
}
