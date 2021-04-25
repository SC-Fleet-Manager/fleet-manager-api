<?php

namespace App\Infrastructure\Repository\Organization;

use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\ConflictVersionException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Webmozart\Assert\Assert;
use function Symfony\Component\String\u;

class DoctrineOrganizationRepository extends ServiceEntityRepository implements OrganizationRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function getOrganization(OrgaId $orgaId): ?Organization
    {
        return $this->findOneBy(['id' => (string)$orgaId]);
    }

    public function save(Organization $orga): void
    {
        $this->_em->persist($orga);
        try {
            $this->_em->flush();
            $this->_em->clear();
        } catch (OptimisticLockException $e) {
            $this->logger->warning('conflict version on save organization.', ['exception' => $e]);
            throw new ConflictVersionException($orga, 'Unable to save your organization. Please, try again.', context: ['orgaId' => $orga->getId()], previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function saveAll(array $organizations): void
    {
        foreach ($organizations as $organization) {
            $this->_em->persist($organization);
        }
        try {
            $this->_em->flush();
            $this->_em->clear();
        } catch (OptimisticLockException $e) {
            $this->logger->warning('conflict version on save organization.', ['exception' => $e]);
            throw new ConflictVersionException($e->getEntity(), 'Unable to save your organizations. Please, try again.', previous: $e);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function deleteAll(array $organizationIds): void
    {
        if (empty($organizationIds)) {
            return;
        }
        Assert::allIsInstanceOf($organizationIds, OrgaId::class);
        $this->_em->createQueryBuilder()
            ->delete(Organization::class, 'orga')
            ->where('orga.id IN (:orgaIds)')
            ->setParameter('orgaIds', $organizationIds)
            ->getQuery()
            ->execute();
    }

    public function getOrganizationBySid(string $sid): ?Organization
    {
        return $this->findOneBy(['sid' => $sid]);
    }

    public function getOrganizationsOfFounder(MemberId $founderId): array
    {
        return $this->findBy(['founderId' => (string)$founderId]);
    }

    public function getOrganizationsByMember(MemberId $memberId): array
    {
        return $this->_em
            ->createQuery(<<<DQL
                SELECT organization, membership FROM App\Entity\Organization organization
                JOIN organization.memberships membership
                WHERE membership.memberId = :memberId
                ORDER BY organization.id ASC
                DQL
            )
            ->setParameter('memberId', $memberId)
            ->getResult();
    }

    public function getOrganizations(int $itemsPerPage, ?OrgaId $sinceOrgaId = null, ?string $searchQuery = null): array
    {
        if ($searchQuery === null) {
            $qb = $this->createQueryBuilder('organization')
                ->orderBy('organization.id', 'ASC')
                ->setMaxResults($itemsPerPage);
            if ($sinceOrgaId !== null) {
                $qb->andWhere('organization.id > :sinceId')->setParameter('sinceId', (string)$sinceOrgaId);
            }

            return $qb->getQuery()->getResult();
        }

        $collator = new \Collator('en');
        $collator->setStrength(\Collator::PRIMARY); // â == A
        $collator->setAttribute(\Collator::ALTERNATE_HANDLING, \Collator::SHIFTED); // ignore punctuations

        $search = u($searchQuery);

        $lastOrgaId = $sinceOrgaId;
        $result = [];
        $countItems = 0;
        do {
            $this->_em->clear();

            $qb = $this->createQueryBuilder('organization')
                ->orderBy('organization.id', 'ASC')
                ->setMaxResults(200);
            if ($lastOrgaId !== null) {
                $qb->andWhere('organization.id > :sinceId')->setParameter('sinceId', (string)$lastOrgaId);
            }
            /** @var Organization[] $orgas */
            $orgas = $qb
                ->getQuery()
                ->getResult(AbstractQuery::HYDRATE_SIMPLEOBJECT);
            $countOrgas = count($orgas);

            foreach ($orgas as $orga) {
                if ($countItems >= $itemsPerPage) {
                    break;
                }
                $lastOrgaId = $orga->getId();
                if (u($orga->getSid())->containsAny($search->upper())) {
                    $result[] = $orga;
                    ++$countItems;
                    continue;
                }
                $name = u($orga->getName());
                $nameLen = $name->length();
                $searchQueryLen = $search->length();

                for ($i = 0; $i <= $nameLen - $searchQueryLen; ++$i) {
                    $partName = $name->slice($i, $searchQueryLen);
                    if ($collator->compare($partName->toString(), $search->toString()) === 0) {
                        $result[] = $orga;
                        ++$countItems;
                        break;
                    }
                }
            }
            unset($orgas);
        } while ($countItems < $itemsPerPage && $countOrgas === 200);

        return $result;
    }
}
