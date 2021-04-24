<?php

namespace App\Infrastructure\Repository\Organization;

use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

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

    public function getOrganizationsByMember(MemberId $memberId): array
    {
        return array_values(array_filter($this->organizations, static function (Organization $orga) use ($memberId): bool {
            return $orga->isMemberOf($memberId);
        }));
    }

    public function getOrganizations(int $itemsPerPage, ?OrgaId $sinceOrgaId = null, ?string $searchQuery = null): array
    {
        $collator = new \Collator('en');
        $collator->setStrength(\Collator::PRIMARY); // â == A
        $collator->setAttribute(\Collator::ALTERNATE_HANDLING, \Collator::SHIFTED); // ignore punctuations

        $collatorSearchQuery = $searchQuery !== null ? $collator->getSortKey($searchQuery) : null;

        $counter = 0;
        $result = [];
        foreach ($this->organizations as $organization) {
            if ($counter >= $itemsPerPage) {
                break;
            }
            if ($collatorSearchQuery !== null) {
                $collatorOrgaName = $collator->getSortKey($organization->getName());
                $collatorOrgaSid = $collator->getSortKey($organization->getSid());
                if (!u($collatorOrgaName)->containsAny($collatorSearchQuery) && !u($collatorOrgaSid)->containsAny($collatorSearchQuery)) {
                    continue;
                }
            }
            if ($sinceOrgaId === null || (string) $organization->getId() > (string) $sinceOrgaId) {
                $result[] = $organization;
                ++$counter;
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function saveAll(array $organizations): void
    {
        foreach ($organizations as $organization) {
            $this->save($organization);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAll(array $organizationIds): void
    {
        Assert::allIsInstanceOf($organizationIds, OrgaId::class);
        foreach ($organizationIds as $orgaId) {
            unset($this->organizations[(string) $orgaId]);
        }
        foreach ($this->organizationsBySid as $orgaBySid) {
            foreach ($organizationIds as $orgaId) {
                if ($orgaBySid->getId()->equals($orgaId)) {
                    unset($this->organizationsBySid[$orgaBySid->getSid()]);
                }
            }
        }
    }
}
