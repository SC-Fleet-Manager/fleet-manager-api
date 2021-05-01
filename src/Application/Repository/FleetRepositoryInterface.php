<?php

namespace App\Application\Repository;

use App\Domain\Exception\AlreadyExistingFleetForUserException;
use App\Domain\Exception\ConflictVersionException;
use App\Domain\UserId;
use App\Entity\Fleet;

interface FleetRepositoryInterface
{
    public function getFleetByUser(UserId $userId): ?Fleet;

    /**
     * @throws AlreadyExistingFleetForUserException
     * @throws ConflictVersionException
     */
    public function save(Fleet $fleet): void;

    public function delete(Fleet $fleet): void;

    public function countShips(): int;
}
