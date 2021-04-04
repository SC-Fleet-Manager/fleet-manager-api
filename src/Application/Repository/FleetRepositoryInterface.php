<?php

namespace App\Application\Repository;

use App\Application\Exception\AlreadyExistingFleetForUserException;
use App\Domain\UserId;
use App\Entity\Fleet;

interface FleetRepositoryInterface
{
    public function getFleetByUser(UserId $userId): ?Fleet;

    /**
     * @throws AlreadyExistingFleetForUserException
     */
    public function save(Fleet $fleet): void;
}
