<?php

namespace App\Service;

use App\Domain\ShipInfo;

interface ShipInfosProviderInterface
{
    /**
     * @return iterable|ShipInfo[]
     */
    public function getAllShips(): iterable;

    public function getShipById(string $id): ?ShipInfo;

    public function getShipByName(string $name): ?ShipInfo;
}
