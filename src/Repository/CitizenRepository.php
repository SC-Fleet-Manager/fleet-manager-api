<?php

namespace App\Repository;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Fleet;
use App\Entity\Ship;
use App\Service\Dto\ShipFamilyFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

class CitizenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Citizen::class);
    }

    /**
     * @return iterable|Citizen[]
     */
    public function getByOrganisation(SpectrumIdentification $organizationId): iterable
    {
        $q = $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.fleets', 'f')
            ->addSelect('f')
            ->leftJoin('f.ships', 's')
            ->addSelect('s')
            ->where('c.organisations LIKE :orga')
            ->setParameter('orga', '%"' . $organizationId . '"%')
            ->getQuery();
        $q->useResultCache(true);
        $q->setResultCacheLifetime(30);

        return $q->getResult();
    }

    /**
     * @return Ship[]
     */
    public function getOrganizationShips(SpectrumIdentification $organizationId, ShipFamilyFilter $filter): array
    {
        $citizenMetadata = $this->getClassMetadata();
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);

        $sql = <<<EOT
            SELECT *, c.id as citizenId, f.id AS fleetId, s.id AS shipId FROM {$citizenMetadata->getTableName()} c 
            INNER JOIN {$fleetMetadata->getTableName()} f ON c.id = f.owner_id AND f.id = (
                SELECT f2.id FROM {$fleetMetadata->getTableName()} f2 WHERE f2.owner_id = f.owner_id ORDER BY f2.version DESC LIMIT 1
            )
            INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id
            WHERE c.organisations LIKE :orgaId 
        EOT;
        // filtering
        if ($filter->shipName !== null) {
            $sql .= ' AND s.name LIKE :shipName ';
        }
        if ($filter->citizenName !== null) {
            $sql .= ' AND c.actual_handle LIKE :citizenName ';
        }

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Ship::class, 's', ['id' => 'shipId']);
        $rsm->addJoinedEntityFromClassMetadata(Fleet::class, 'f', 's', 'fleet', ['id' => 'fleetId']);
        $rsm->addJoinedEntityFromClassMetadata(Citizen::class, 'c', 'f', 'owner', ['id' => 'citizenId']);

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameter(':orgaId', '%"' . $organizationId . '"%');
        if ($filter->shipName !== null) {
            $stmt->setParameter('shipName', '%' . $filter->shipName . '%');
        }
        if ($filter->citizenName !== null) {
            $stmt->setParameter('citizenName', '%' . $filter->citizenName . '%');
        }

        return $stmt->getResult();
    }

    public function countOwnedShips(string $organizationId, string $shipName): int
    {
        $citizenMetadata = $this->getClassMetadata();
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);

        /*
        TODO : more opti ?
        select count(*) from ship s inner join fleet f on f.id = s.fleet_id and lower(s.name) = :shipName
            inner join citizen c on c.id = f.owner_id and f.id = (
            select f2.id
            from fleet f2
            where f2.owner_id = f.owner_id
            order by f2.version desc
            limit 1
        )
        where c.organisations LIKE :orgaId
        */

        $sql = <<<EOT
            SELECT count(*) as countOwned FROM {$citizenMetadata->getTableName()} c 
            INNER JOIN {$fleetMetadata->getTableName()} f ON c.id = f.owner_id AND f.id = (
                SELECT f2.id FROM {$fleetMetadata->getTableName()} f2 WHERE f2.owner_id = f.owner_id ORDER BY f2.version DESC LIMIT 1
            )
            INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id and LOWER(s.name) = :shipName 
            WHERE c.organisations LIKE :orgaId 
        EOT;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('countOwned', 'countOwned');
        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'orgaId' => '%"' . $organizationId . '"%',
            'shipName' => mb_strtolower($shipName),
        ]);

        return $stmt->getSingleScalarResult();
    }

    public function countOwnersOfShip(string $organizationId, string $shipName): int
    {
        $citizenMetadata = $this->getClassMetadata();
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);

        $sql = <<<EOT
            SELECT count(DISTINCT c.id) as countOwners FROM {$citizenMetadata->getTableName()} c 
            INNER JOIN {$fleetMetadata->getTableName()} f ON c.id = f.owner_id AND f.id = (
                SELECT f2.id FROM {$fleetMetadata->getTableName()} f2 WHERE f2.owner_id = f.owner_id ORDER BY f2.version DESC LIMIT 1
            )
            INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id and LOWER(s.name) = :shipName 
            WHERE c.organisations LIKE :orgaId 
        EOT;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('countOwners', 'countOwners');
        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'orgaId' => '%"' . $organizationId . '"%',
            'shipName' => mb_strtolower($shipName),
        ]);

        return $stmt->getSingleScalarResult();
    }

    public function getOwnersOfShip(string $organizationId, string $shipName, int $page = null, int $itemsPerPage = 10): iterable
    {
        $citizenMetadata = $this->getClassMetadata();
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);

        $sql = <<<EOT
            SELECT c.*, c.id as citizenId, COUNT(s.id) as countShips FROM {$citizenMetadata->getTableName()} c
            INNER JOIN {$fleetMetadata->getTableName()} f ON c.id = f.owner_id AND f.id = (
                SELECT f2.id FROM {$fleetMetadata->getTableName()} f2 WHERE f2.owner_id = f.owner_id ORDER BY f2.version DESC LIMIT 1
            )
            INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id and LOWER(s.name) = :shipName
            WHERE c.organisations LIKE :orgaId
            GROUP BY c.id
            ORDER BY countShips DESC
        EOT;
        if ($page !== null) {
            $sql .= "\nLIMIT :first, :countItems\n";
        }

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Citizen::class, 'c', ['id' => 'citizenId']);
        $rsm->addScalarResult('countShips', 'countShips');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'orgaId' => '%"' . $organizationId . '"%',
            'shipName' => mb_strtolower($shipName),
        ]);
        if ($page !== null) {
            $page = $page < 1 ? 1 : $page;
            $stmt->setParameter('first', ($page - 1) * $itemsPerPage);
            $stmt->setParameter('countItems', $itemsPerPage);
        }

        return $stmt->getResult();
    }
}
