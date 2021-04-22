<?php

namespace App\Infrastructure\Provider\Organizations;

use App\Application\Provider\UserFleetProviderInterface;
use App\Domain\MemberId;
use App\Domain\UserFleet;

class InMemoryUserFleetProvider implements UserFleetProviderInterface
{
    private UserFleet $userFleet;

    public function setUserFleet(UserFleet $userFleet): void
    {
        $this->userFleet = $userFleet;
    }

    public function getUserFleet(MemberId $memberId): UserFleet
    {
        return $this->userFleet;
    }
}
