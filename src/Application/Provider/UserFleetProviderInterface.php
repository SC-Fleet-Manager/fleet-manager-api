<?php

namespace App\Application\Provider;

use App\Domain\MemberId;
use App\Domain\UserFleet;

interface UserFleetProviderInterface
{
    public function getUserFleet(MemberId $memberId): UserFleet;
}
