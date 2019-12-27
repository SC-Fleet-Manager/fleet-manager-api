<?php

namespace App\Repository;

use App\Entity\ShipChassis;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ShipChassisRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ShipChassis::class);
    }

    public function findAllChassisNames(): array
    {
        $dql = <<<DQL
                SELECT s.rsiId, s.name FROM App\Entity\ShipChassis s INDEX BY s.rsiId
            DQL;

        $query = $this->_em->createQuery($dql);
        $query->enableResultCache(30);

        return $query->getArrayResult();
    }
}
