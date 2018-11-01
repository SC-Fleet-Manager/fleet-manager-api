<?php

namespace App\Domain;

interface FleetRepositoryInterface
{
    function save(Fleet $fleet): void;

    function getLastVersionFleet(Citizen $citizen): int;
}
