<?php

namespace App\Application\Repository;

use App\Domain\UserId;
use App\Entity\Fleet;

interface FleetRepositoryInterface
{
    public function getFleetByUser(UserId $userId): ?Fleet;
}
