<?php

namespace App\Infrastructure\Repository\Organization;

use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Domain\OrgaId;
use App\Entity\OrganizationFleet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareTrait;
use Webmozart\Assert\Assert;

class DoctrineOrganizationFleetRepository extends ServiceEntityRepository implements OrganizationFleetRepositoryInterface
{
    use LoggerAwareTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrganizationFleet::class);
    }

    public function getOrganizationFleet(OrgaId $orgaId): ?OrganizationFleet
    {
        return $this->findOneBy(['orgaId' => (string) $orgaId]);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrganizationFleets(array $orgaIds): array
    {
        if (empty($orgaIds)) {
            return [];
        }
        Assert::allIsInstanceOf($orgaIds, OrgaId::class);

        return $this->findBy(['orgaId' => $orgaIds]);
    }

    /**
     * {@inheritDoc}
     */
    public function saveAll(array $organizationFleets): void
    {
        foreach ($organizationFleets as $organizationFleet) {
            $this->_em->persist($organizationFleet);
        }
        $this->_em->flush();
        $this->_em->clear();
    }
}
