<?php

namespace App\Application\Repository;

use App\Domain\Exception\ConflictVersionException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;

interface OrganizationRepositoryInterface
{
    public function getOrganization(OrgaId $orgaId): ?Organization;

    /**
     * @throws ConflictVersionException
     */
    public function save(Organization $orga): void;

    /**
     * @param Organization[] $organizations
     */
    public function saveAll(array $organizations): void;

    /**
     * @param OrgaId[] $organizationIds
     */
    public function deleteAll(array $organizationIds): void;

    public function getOrganizationBySid(string $sid): ?Organization;

    /**
     * @return Organization[]
     */
    public function getOrganizationsOfFounder(MemberId $founderId): array;

    /**
     * @return Organization[]
     */
    public function getOrganizationsByMember(MemberId $memberId): array;

    /**
     * @return Organization[]
     */
    public function getOrganizations(int $itemsPerPage, ?OrgaId $sinceOrgaId = null, ?string $searchQuery = null): array;
}
