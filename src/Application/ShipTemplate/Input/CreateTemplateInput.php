<?php

namespace App\Application\ShipTemplate\Input;

use App\Entity\CargoCapacity;
use App\Entity\ShipChassis;
use App\Entity\Crew;
use App\Entity\Manufacturer;
use App\Entity\Price;
use App\Entity\ShipRole;
use App\Entity\ShipSize;
use Webmozart\Assert\Assert;

class CreateTemplateInput
{
    public function __construct(
        public string $model,
        public ?string $pictureUrl,
        public ShipChassis $chassis,
        public Manufacturer $manufacturer,
        public ShipSize $size,
        public ShipRole $role,
        public CargoCapacity $cargoCapacity,
        public Crew $crew,
        public Price $price,
    ) {
        Assert::startsWith($this->pictureUrl ?? 'http', 'http');
        Assert::lengthBetween($this->model, 2, 60);
        Assert::maxLength($this->pictureUrl, 1023);
    }
}
