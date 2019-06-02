<?php

namespace App\Event;

use App\Entity\Citizen;
use Symfony\Component\EventDispatcher\Event;

class CitizenRefreshEvent extends Event
{
    public const NAME = 'citizen_refresh';

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
