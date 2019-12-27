<?php

namespace App\Repository;

use App\Entity\CitizenOrganization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class CitizenOrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CitizenOrganization::class);
    }

    public function findGreaterThanRank(string $sid, int $rank): array
    {
        $qb = $this->createQueryBuilder('co');
        $query = $qb->where('co.rank > :minRank')
            ->andWhere('co.organizationSid = :sid')
            ->setParameters([
                'minRank' => $rank,
                'sid' => $sid,
            ])
            ->getQuery();

        return $query->getResult();
    }
}
