<?php

namespace App\Repository;

use App\Entity\Citizen;
use App\Entity\Fleet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class FleetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fleet::class);
    }

    public function getLastVersionFleet(Citizen $citizen): ?Fleet
    {
        $qb = $this->createQueryBuilder('f');
        $q = $qb
            ->select('f')
            ->join('f.owner', 'o')
            ->addSelect('o')
            ->leftJoin('f.ships', 's')
            ->addSelect('s')
            ->where('f.owner = :owner')
            ->orderBy('f.version', 'DESC')
            ->setParameter('owner', $citizen->getId())
            ->setMaxResults(1)
            ->getQuery();

        return $q->getOneOrNullResult();
    }
}
