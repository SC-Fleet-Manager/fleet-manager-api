<?php

namespace App\Event;

use App\Entity\Funding;
use Symfony\Contracts\EventDispatcher\Event;

class FundingUpdatedEvent extends Event
{
    private Funding $funding;

    public function __construct(Funding $funding)
    {
        $this->funding = $funding;
    }

    public function getFunding(): Funding
    {
        return $this->funding;
    }
}
