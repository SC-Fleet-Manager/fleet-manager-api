<?php

namespace App\Application\Repository;

use App\Domain\OrgaId;
use App\Entity\OrganizationFleet;

interface OrganizationFleetRepositoryInterface
{
    public function getOrganizationFleet(OrgaId $orgaId): ?OrganizationFleet;

    /**
     * @return OrganizationFleet[]
     */
    public function getOrganizationFleets(array $orgaIds): array;

    /**
     * @param OrganizationFleet[] $organizationFleets
     */
    public function saveAll(array $organizationFleets): void;
}
