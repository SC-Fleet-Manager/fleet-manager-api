<?php

namespace App\Repository;

use App\Entity\Citizen;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OrganizationRepository extends ServiceEntityRepository
{
    use OrganizationStatisticsRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    /**
     * @return Citizen[]
     */
    public function findCitizensBySid(string $sid): array
    {
        $dql = <<<DQL
            SELECT c FROM App\Entity\Citizen c
            INNER JOIN c.organizations co
            INNER JOIN co.organization o
            WHERE o.organizationSid = :sid
            ORDER BY c.actualHandle ASC
            DQL;
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sid', $sid);

        return $query->getResult();
    }
}
