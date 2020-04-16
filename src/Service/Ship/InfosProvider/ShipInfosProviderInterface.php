<?php

namespace App\Service\Ship\InfosProvider;

use App\Domain\ShipInfo;

interface ShipInfosProviderInterface
{
    /**
     * @return ShipInfo[]
     */
    public function getAllShips(): array;

    /**
     * @return ShipInfo[] indexed by Id
     */
    public function getShipsByIdOrName(array $ids, array $names = []): array;

    /**
     * typically delete the cache and warmup.
     *
     * @return ShipInfo[]
     */
    public function refreshShips(): array;

    /**
     * @return ShipInfo[]
     */
    public function getShipsByChassisId(string $chassisId): array;

    public function getShipById(string $id): ShipInfo;
}
