<?php

namespace App\Domain;

interface ShipInfosProviderInterface
{
    /**
     * @return iterable|ShipInfo[]
     */
    public function getAllShips(): iterable;

    public function getShipById(string $id): ?ShipInfo;

    public function getShipByName(string $name): ?ShipInfo;
}
