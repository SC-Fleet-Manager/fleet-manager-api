<?php

namespace App\Infrastructure\Repository\Organization;

use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Domain\OrgaId;
use App\Entity\OrganizationFleet;

class InMemoryOrganizationFleetRepository implements OrganizationFleetRepositoryInterface
{
    /** @var OrganizationFleet[] */
    private array $organizationFleets;

    /**
     * @param OrganizationFleet[] $organizationFleets
     */
    public function setOrganizationFleets(array $organizationFleets): void
    {
        foreach ($organizationFleets as $organizationFleet) {
            $this->organizationFleets[(string) $organizationFleet->getOrgaId()] = $organizationFleet;
        }
    }

    public function getOrganizationFleet(OrgaId $orgaId): ?OrganizationFleet
    {
        return $this->organizationFleets[(string) $orgaId] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getOrganizationFleets(array $orgaIds): array
    {
        $result = [];
        foreach ($orgaIds as $orgaId) {
            if (isset($this->organizationFleets[$orgaId])) {
                $result[] = $this->organizationFleets[$orgaId];
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function saveAll(array $organizationFleets): void
    {
        $this->setOrganizationFleets($organizationFleets);
    }
}