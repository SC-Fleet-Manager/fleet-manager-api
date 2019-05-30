<?php

namespace App\Service\Dto;

class ShipFamilyFilter
{
    /** @var string */
    public $shipName;

    /** @var string */
    public $citizenName;

    public function __construct(?string $shipName, ?string $citizenName)
    {
        $this->shipName = $shipName;
        $this->citizenName = $citizenName;
    }
}
