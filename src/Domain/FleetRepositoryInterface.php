<?php

namespace App\Domain;

interface FleetRepositoryInterface
{
    public function save(Fleet $fleet): void;

    public function getLastVersionFleet(Citizen $citizen): ?Fleet;

    /**
     * @return iterable|Fleet[]
     */
    public function all(): iterable;
}
