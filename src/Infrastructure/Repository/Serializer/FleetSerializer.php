<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Serializer;

use App\Domain\Fleet as DomainFleet;
use App\Domain\Ship as DomainShip;
use App\Infrastructure\Entity\Fleet;

class FleetSerializer
{
    /**
     * @var ShipSerializer
     */
    private $shipSerializer;

    /**
     * @var CitizenSerializer
     */
    private $citizenSerializer;

    public function __construct(ShipSerializer $shipSerializer, CitizenSerializer $citizenSerializer)
    {
        $this->shipSerializer = $shipSerializer;
        $this->citizenSerializer = $citizenSerializer;
    }

    public function toDomain(Fleet $fleetEntity, ?DomainFleet $fleet = null): DomainFleet
    {
        if ($fleet === null) {
            $citizen = $this->citizenSerializer->toDomain($fleetEntity->owner);
            $fleet = new DomainFleet($fleetEntity->id, $citizen);
        }
        foreach ($fleetEntity->ships as $shipEntity) {
            $ship = new DomainShip($shipEntity->id, $fleet->owner);
            $this->shipSerializer->toDomain($shipEntity, $ship);
            $fleet->ships[] = $ship;
        }
        $fleet->uploadDate = clone $fleetEntity->uploadDate;
        $fleet->version = $fleetEntity->version;

        return $fleet;
    }

    public function fromDomain(DomainFleet $fleet): Fleet
    {
        $e = new Fleet();
        $e->id = clone $fleet->id;
        $e->owner = $this->citizenSerializer->fromDomain($fleet->owner);
        $e->uploadDate = clone $fleet->uploadDate;
        $e->version = $fleet->version;

        return $e;
    }
}
