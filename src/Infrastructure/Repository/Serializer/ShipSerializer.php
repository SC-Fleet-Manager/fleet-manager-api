<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Serializer;

use App\Domain\Money;
use App\Domain\Ship as DomainShip;
use App\Infrastructure\Entity\Ship;

class ShipSerializer
{
    /**
     * @var CitizenSerializer
     */
    private $citizenSerializer;

    public function __construct(CitizenSerializer $citizenSerializer)
    {
        $this->citizenSerializer = $citizenSerializer;
    }

    public function toDomain(Ship $shipEntity, ?DomainShip $ship = null): DomainShip
    {
        if ($ship === null) {
            $citizen = $this->citizenSerializer->toDomain($shipEntity->owner);
            $ship = new DomainShip($shipEntity->id, $citizen);
        }
        $ship->name = $shipEntity->name;
        $ship->cost = new Money($shipEntity->cost);
        $ship->insured = $shipEntity->insured;
        $ship->manufacturer = $shipEntity->manufacturer;
        $ship->pledgeDate = clone $shipEntity->pledgeDate;
        $ship->rawData = $shipEntity->rawData;

        return $ship;
    }

    public function fromDomain(DomainShip $ship): Ship
    {
        $e = new Ship();
        $e->id = clone $ship->id;
        $e->owner = $this->citizenSerializer->fromDomain($ship->owner);
        $e->rawData = $ship->rawData;
        $e->pledgeDate = clone $ship->pledgeDate;
        $e->cost = $ship->cost->getCost();
        $e->insured = $ship->insured;
        $e->name = $ship->name;
        $e->manufacturer = $ship->manufacturer;

        return $e;
    }
}
