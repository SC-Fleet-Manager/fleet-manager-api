<?php

namespace App\Event;

use App\Entity\Citizen;
use Symfony\Contracts\EventDispatcher\Event;

class CitizenDeletedEvent extends Event
{
    private $deletedCitizen;

    public function __construct(Citizen $deletedCitizen)
    {
        $this->deletedCitizen = $deletedCitizen;
    }

    public function getDeletedCitizen(): Citizen
    {
        return $this->deletedCitizen;
    }
}
