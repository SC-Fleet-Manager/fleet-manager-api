<?php

namespace App\Infrastructure\Repository\Organization;

use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\ConflictVersionException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class DoctrineOrganizationRepository extends ServiceEntityRepository implements OrganizationRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function getOrganization(OrgaId $orgaId): ?Organization
    {
        return $this->findOneBy(['id' => (string) $orgaId]);
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

    public function getOrganizationBySid(string $sid): ?Organization
    {
        return $this->findOneBy(['sid' => $sid]);
    }

    public function getOrganizationsOfFounder(MemberId $founderId): array
    {
        return $this->findBy(['founderId' => (string) $founderId]);
    }

    public function getOrganizationWithoutMembersByMember(MemberId $memberId): array
    {
        return $this->_em
            ->createQuery(<<<DQL
                SELECT organization, membership FROM App\Entity\Organization organization
                JOIN organization.memberships membership
                WHERE membership.memberId = :memberId
                DQL
            )
            ->setParameter('memberId', $memberId)
            ->getResult();
    }
}
