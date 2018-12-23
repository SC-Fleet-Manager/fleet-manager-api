<?php

namespace App\Domain;

interface FleetUploadHandlerInterface
{
    /**
     * @param Citizen $citizen
     * @param array   $fleetData
     */
    public function handle(Citizen $citizen, array $fleetData): void;
}
