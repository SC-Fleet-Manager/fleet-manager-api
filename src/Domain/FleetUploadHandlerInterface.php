<?php

namespace App\Domain;

interface FleetUploadHandlerInterface
{
    /**
     * @param HandleSC $handleSC
     * @param array $fleetData
     */
    function handle(HandleSC $handleSC, array $fleetData): void;
}
