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

    public function countTotalShips(): int
    {
        $fleetMetadata = $this->getClassMetadata();
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);

        $sql = <<<EOT
            SELECT count(*) as countShips FROM {$citizenMetadata->getTableName()} c 
            INNER JOIN {$fleetMetadata->getTableName()} f ON c.id = f.owner_id AND f.id = (
                SELECT f2.id FROM {$fleetMetadata->getTableName()} f2 WHERE f2.owner_id = f.owner_id ORDER BY f2.version DESC LIMIT 1
            )
            INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id
        EOT;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addScalarResult('countShips', 'countShips');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);

        return $stmt->getSingleScalarResult();
    }
}
