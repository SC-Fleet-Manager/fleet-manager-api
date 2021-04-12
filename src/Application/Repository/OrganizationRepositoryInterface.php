<?php

namespace App\Application\Repository;

use App\Domain\Exception\ConflictVersionException;
use App\Domain\OrgaId;
use App\Domain\UserId;
use App\Entity\Organization;

interface OrganizationRepositoryInterface
{
    public function getOrganization(OrgaId $orgaId): ?Organization;

    /**
     * @throws ConflictVersionException
     */
    public function save(Organization $orga): void;

    public function getOrganizationBySid(string $sid): ?Organization;

    /**
     * @return Organization[]
     */
    public function getOrganizationsOfFounder(UserId $founderId): array;
}
