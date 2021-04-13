<?php

namespace App\Infrastructure\Repository\Organization;

use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\UserId;
use App\Entity\Organization;

class InMemoryOrganizationRepository implements OrganizationRepositoryInterface
{
    /** @var Organization[] */
    private array $organizations;
    /** @var Organization[] */
    private array $organizationsBySid;

    public function getOrganization(OrgaId $orgaId): ?Organization
    {
        return $this->organizations[(string) $orgaId] ?? null;
    }

    public function getOrganizationBySid(string $sid): ?Organization
    {
        return $this->organizationsBySid[$sid] ?? null;
    }

    public function getOrganizationsOfFounder(MemberId $founderId): array
    {
        return array_filter($this->organizations, static function (Organization $orga) use ($founderId): bool {
            return $orga->getFounderId()->equals($founderId);
        });
    }

    public function save(Organization $orga): void
    {
        $this->organizations[(string) $orga->getId()] = $orga;
        $this->organizationsBySid[$orga->getSid()] = $orga;
    }

    public function getOrganizationWithoutMembersByMember(MemberId $memberId): array
    {
        return array_values(array_filter($this->organizations, static function (Organization $orga) use ($memberId): bool {
            return $orga->isMemberOf($memberId);
        }));
    }
}
