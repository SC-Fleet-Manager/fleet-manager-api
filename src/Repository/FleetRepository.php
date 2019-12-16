<?php

namespace App\Repository;

use App\Entity\Citizen;
use App\Entity\Fleet;
use App\Entity\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class FleetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fleet::class);
    }

    public function countTotalShips(): int
    {
        $fleetMetadata = $this->getClassMetadata();
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);

        $sql = <<<EOT
                SELECT count(*) as countShips FROM {$citizenMetadata->getTableName()} c 
                INNER JOIN {$fleetMetadata->getTableName()} f ON f.id = c.last_fleet_id
                INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id
            EOT;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addScalarResult('countShips', 'countShips');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->enableResultCache(300);

        return $stmt->getSingleScalarResult();
    }
}
