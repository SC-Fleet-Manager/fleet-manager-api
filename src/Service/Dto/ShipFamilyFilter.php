<?php

namespace App\Service\Dto;

class ShipFamilyFilter
{
    /** @var string[] */
    public array $shipGalaxyIds;

    /** @var string[] */
    public array $citizenIds;

    /** @var string[] */
    public array $shipSizes;

    public ?string $shipStatus;

    public function __construct(array $shipGalaxyIds = [], array $citizenIds = [], array $shipSizes = [], ?string $shipStatus = null)
    {
        $this->shipGalaxyIds = $shipGalaxyIds;
        $this->citizenIds = $citizenIds;
        $this->shipSizes = $shipSizes;
        $this->shipStatus = $shipStatus;
    }
}
