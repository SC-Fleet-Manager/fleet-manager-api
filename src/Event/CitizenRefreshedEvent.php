<?php

namespace App\Event;

use App\Domain\CitizenInfos;
use App\Entity\Citizen;
use Symfony\Contracts\EventDispatcher\Event;

class CitizenRefreshedEvent extends Event
{
    private Citizen $citizenBeforeChange;
    private CitizenInfos $citizenInfos;

    public function __construct(Citizen $citizenBeforeChange, CitizenInfos $citizenInfos)
    {
        $this->citizenBeforeChange = $citizenBeforeChange;
        $this->citizenInfos = $citizenInfos;
    }

    public function getCitizenBeforeChange(): Citizen
    {
        return $this->citizenBeforeChange;
    }

    public function getCitizenInfos(): CitizenInfos
    {
        return $this->citizenInfos;
    }
}
