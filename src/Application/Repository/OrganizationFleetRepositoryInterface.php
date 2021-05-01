<?php

namespace App\Application\Repository;

use App\Domain\OrgaId;
use App\Entity\OrganizationFleet;

interface OrganizationFleetRepositoryInterface
{
    public function getOrganizationFleet(OrgaId $orgaId): ?OrganizationFleet;

    /**
     * @param OrgaId[] $orgaIds
     *
     * @return OrganizationFleet[]
     */
    public function getOrganizationFleets(array $orgaIds): array;

    /**
     * @param OrganizationFleet[] $organizationFleets
     */
    public function saveAll(array $organizationFleets): void;

    /**
     * @param OrgaId[] $orgaIds
     */
    public function deleteAll(array $orgaIds): void;

    public function countFleets(): int;
}
