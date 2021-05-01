<?php

namespace App\Infrastructure\Repository\Organization;

use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Domain\OrgaId;
use App\Entity\OrganizationFleet;

class InMemoryOrganizationFleetRepository implements OrganizationFleetRepositoryInterface
{
    /** @var OrganizationFleet[] */
    private array $organizationFleets = [];

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
            if (isset($this->organizationFleets[(string) $orgaId])) {
                $result[] = $this->organizationFleets[(string) $orgaId];
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

    /**
     * {@inheritDoc}
     */
    public function deleteAll(array $orgaIds): void
    {
        foreach ($orgaIds as $orgaId) {
            unset($this->organizationFleets[(string) $orgaId]);
        }
    }

    public function countFleets(): int
    {
        return count($this->organizationFleets);
    }
}
