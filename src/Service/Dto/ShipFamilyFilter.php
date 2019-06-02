<?php

namespace App\Service\Dto;

class ShipFamilyFilter
{
    /** @var string[] */
    public $shipNames;

    /** @var string[] */
    public $citizenIds;

    /** @var string[] */
    public $shipSizes;

    public function __construct(array $shipNames = [], array $citizenIds = [], array $shipSizes = [])
    {
        $this->shipNames = $shipNames;
        $this->citizenIds = $citizenIds;
        $this->shipSizes = $shipSizes;
    }
}
