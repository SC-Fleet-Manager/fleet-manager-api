<?php

namespace App\Repository;

use App\Entity\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ShipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ship::class);
    }

    public function distinctNames(): iterable
    {
        $qb = $this->createQueryBuilder('ships')
            ->distinct()
            ->select('ships.name')
            ->orderBy('ships.name');

        return array_map(function (array $result): string {
            return $result['name'];
        }, $qb->getQuery()->getScalarResult());
    }
}
