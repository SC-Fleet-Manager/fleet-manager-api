<?php

namespace App\Service\Dto;

class ShipFamilyFilter
{
    /** @var string[] */
    public $shipNames;

    /** @var string[] */
    public $citizenIds;

    public function __construct(array $shipNames = [], array $citizenIds = [])
    {
        $this->shipNames = $shipNames;
        $this->citizenIds = $citizenIds;
    }
}
