<?php

namespace App\Repository;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\Fleet;
use App\Entity\Organization;
use App\Entity\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class ShipRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ship::class);
    }

    /**
     * @return Ship[]
     */
    public function findFromLastFleetWithoutGalaxyId(?UuidInterface $afterId = null, ?int $itemPerPage = null): iterable
    {
        $dql = <<<DQL
            SELECT ship FROM  App\Entity\Citizen citizen
            JOIN citizen.lastFleet fleet
            JOIN App\Entity\Ship ship WITH ship.fleet = fleet.id
            WHERE (ship.galaxyId IS NULL OR ship.normalizedName IS NULL)
            DQL;
        if ($afterId !== null) {
            $dql .= ' AND ship.id > :afterId ';
        }
        $dql .= ' ORDER BY ship.id ASC ';
        $query = $this->_em->createQuery($dql);

        if ($afterId !== null) {
            $query->setParameter('afterId', $afterId);
        }
        if ($itemPerPage !== null) {
            $query->setMaxResults($itemPerPage);
        }

        return $query->getResult();
    }

    /**
     * @return Ship[]
     */
    public function getFiltrableOrganizationShipNames(SpectrumIdentification $organizationId): array
    {
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);

        $sql = <<<EOT
                SELECT DISTINCT s.name AS shipName FROM {$citizenMetadata->getTableName()} c
                INNER JOIN {$citizenOrgaMetadata->getTableName()} citizenOrga ON citizenOrga.citizen_id = c.id
                INNER JOIN {$orgaMetadata->getTableName()} orga ON orga.id = citizenOrga.organization_id
                INNER JOIN {$fleetMetadata->getTableName()} f ON c.id = f.owner_id AND f.id = (
                    SELECT f2.id FROM {$fleetMetadata->getTableName()} f2 WHERE f2.owner_id = f.owner_id ORDER BY f2.version DESC LIMIT 1
                )
                INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id
                WHERE orga.organization_sid = :sid
                ORDER BY s.name
            EOT;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addScalarResult('shipName', 'shipName');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameter('sid', mb_strtolower($organizationId->getSid()));

        return $stmt->getResult();
    }
}
