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

    /** @var string|null */
    public $shipStatus;

    public function __construct(array $shipNames = [], array $citizenIds = [], array $shipSizes = [], ?string $shipStatus = null)
    {
        $this->shipNames = $shipNames;
        $this->citizenIds = $citizenIds;
        $this->shipSizes = $shipSizes;
        $this->shipStatus = $shipStatus;
    }
}
