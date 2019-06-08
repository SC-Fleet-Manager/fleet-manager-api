<?php

namespace App\Event;

use App\Entity\Citizen;
use Symfony\Contracts\EventDispatcher\Event;

class CitizenRefreshEvent extends Event
{
    private $citizen;

    public function __construct(Citizen $citizen)
    {
        $this->citizen = $citizen;
    }

    public function getCitizen(): Citizen
    {
        return $this->citizen;
    }
}
