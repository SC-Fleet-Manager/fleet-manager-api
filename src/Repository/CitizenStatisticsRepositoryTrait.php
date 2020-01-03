<?php

namespace App\Repository;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\Fleet;
use App\Entity\Organization;
use App\Entity\Ship;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * @property EntityManager $_em
 */
trait CitizenStatisticsRepositoryTrait
{
    public function statCountCitizensByOrga(SpectrumIdentification $sid): ?int
    {
        $dql = '
            SELECT COUNT(c.id) as countCitizens FROM App\Entity\Citizen c
            INNER JOIN c.organizations citizenOrga
            INNER JOIN citizenOrga.organization orga WITH orga.organizationSid = :sid
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sid', $sid->getSid());
        $query->enableResultCache(300);

        return $query->getSingleScalarResult();
    }

    public function statAverageShipsPerCitizenByOrga(SpectrumIdentification $sid): ?float
    {
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);

        $sql = <<<EOT
                SELECT AVG(countShips) AS avgShips FROM (
                    SELECT COUNT(s.id) AS countShips FROM {$citizenMetadata->getTableName()} c
                    INNER JOIN {$citizenOrgaMetadata->getTableName()} citizenOrga ON citizenOrga.citizen_id = c.id
                    INNER JOIN {$orgaMetadata->getTableName()} orga ON orga.id = citizenOrga.organization_id AND orga.organization_sid = :sid
                    INNER JOIN {$fleetMetadata->getTableName()} f ON f.id = c.last_fleet_id
                    LEFT JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id
                    GROUP BY c.id
                ) CountShips
            EOT;

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('avgShips', 'avgShips');
        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameter('sid', $sid->getSid());
        $stmt->enableResultCache(300);

        return $stmt->getSingleScalarResult();
    }

    public function statCitizenWithMostShipsByOrga(SpectrumIdentification $sid): ?array
    {
        $dql = '
            SELECT c, citizenOrga, orga, COUNT(ship.id) as maxShip FROM App\Entity\Citizen c
            INNER JOIN c.organizations citizenOrga
            INNER JOIN citizenOrga.organization orga WITH orga.organizationSid = :sid
            INNER JOIN c.lastFleet fleet
            INNER JOIN fleet.ships ship
            GROUP BY c.id, citizenOrga.id
            ORDER BY maxShip DESC
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sid', $sid->getSid());
        $query->enableResultCache(300);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    public function statShipsPerCitizenByOrga(SpectrumIdentification $sid): array
    {
        $dql = '
            SELECT c, citizenOrga, mainCitizenOrga, mainOrga, orga, COUNT(ship.id) as countShips FROM App\Entity\Citizen c
            INNER JOIN c.organizations citizenOrga
            INNER JOIN citizenOrga.organization orga WITH orga.organizationSid = :sid
            INNER JOIN c.lastFleet fleet
            LEFT JOIN fleet.ships ship
            LEFT JOIN c.mainOrga mainCitizenOrga
            LEFT JOIN mainCitizenOrga.organization mainOrga
            GROUP BY c.id, citizenOrga.id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sid', $sid->getSid());
        $query->enableResultCache(300);

        return $query->getResult();
    }
}
