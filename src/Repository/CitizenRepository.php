<?php

namespace App\Repository;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\Fleet;
use App\Entity\Organization;
use App\Entity\Ship;
use App\Entity\User;
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
    public function getByOrganization(SpectrumIdentification $organizationId): iterable
    {
        $dql = '
            SELECT c, f, s FROM App\Entity\Citizen c
            INNER JOIN c.organizations citizenOrga
            INNER JOIN citizenOrga.organization orga WITH orga.organizationSid = :sid
            LEFT JOIN c.fleets f
            LEFT JOIN f.ships s
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sid', mb_strtolower($organizationId->getSid()));
        $query->useResultCache(true);
        $query->setResultCacheLifetime(30);

        return $query->getResult();
    }

    /**
     * @param Citizen|null $viewerCitizen the logged citizen
     *
     * @return Citizen[]
     */
    public function findVisiblesByOrganization(string $organizationId, ?Citizen $viewerCitizen): array
    {
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);
        $userMetadata = $this->_em->getClassMetadata(User::class);

        $sql = <<<EOT
            SELECT c.*, c.id AS citizenId
            FROM {$orgaMetadata->getTableName()} orga
            INNER JOIN {$citizenOrgaMetadata->getTableName()} citizenOrga ON orga.id = citizenOrga.organization_id AND orga.organization_sid = :sid
            INNER JOIN {$citizenMetadata->getTableName()} c ON citizenOrga.citizen_id = c.id
            INNER JOIN {$userMetadata->getTableName()} u ON u.citizen_id = c.id
            WHERE (
                u.public_choice = :userPublicChoicePublic
                OR (u.public_choice = :userPublicChoiceOrga AND (
                        # visibility ORGA : visible by everyone
                        # visibility ADMIN : visible only by highest orga rank
                        # visibility PRIVATE : visible by anybody
                        citizenOrga.visibility = :visibilityOrga
                        OR (citizenOrga.visibility = :visibilityAdmin AND :viewerCitizenId IS NOT NULL AND :viewerCitizenId IN (
                                # select highest ranks of this orga
                                SELECT co.citizen_id
                                FROM {$orgaMetadata->getTableName()} o
                                INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
                                WHERE co.rank = (
                                    SELECT max(co.rank) AS maxRank
                                    FROM {$orgaMetadata->getTableName()} o
                                    INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
                                )
                            )
                        )
                    )
                )
            )
        EOT;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Citizen::class, 'c', ['id' => 'citizenId']);

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'sid' => mb_strtolower($organizationId),
            'visibilityOrga' => CitizenOrganization::VISIBILITY_ORGA,
            'visibilityAdmin' => CitizenOrganization::VISIBILITY_ADMIN,
            'userPublicChoicePublic' => User::PUBLIC_CHOICE_PUBLIC,
            'userPublicChoiceOrga' => User::PUBLIC_CHOICE_ORGANIZATION,
            'viewerCitizenId' => $viewerCitizen !== null ? $viewerCitizen->getId()->toString() : null,
        ]);
        $stmt->useResultCache(true, 30);

        return $stmt->getResult();
    }

    /**
     * @return Citizen[]
     */
    public function findAdminByOrganization(string $organizationSid): array
    {
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);

        $sql = <<<EOT
            SELECT c.*, c.id AS citizenId
            FROM {$orgaMetadata->getTableName()} o
            INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
            INNER JOIN {$citizenMetadata->getTableName()} c ON c.id = co.citizen_id
            WHERE co.rank = (
                SELECT max(co.rank) AS maxRank
                FROM {$orgaMetadata->getTableName()} o
                INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
            )
        EOT;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Citizen::class, 'c', ['id' => 'citizenId']);

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'sid' => mb_strtolower($organizationSid),
        ]);
        $stmt->useResultCache(true, 30);

        return $stmt->getResult();
    }

    /**
     * @return Ship[]
     */
    public function getOrganizationShips(SpectrumIdentification $organizationId, ShipFamilyFilter $filter): array
    {
        $citizenMetadata = $this->getClassMetadata();
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);

        $sql = <<<EOT
            SELECT *, c.id as citizenId, f.id AS fleetId, s.id AS shipId FROM {$citizenMetadata->getTableName()} c 
            INNER JOIN {$citizenOrgaMetadata->getTableName()} citizenOrga ON citizenOrga.citizen_id = c.id
            INNER JOIN {$orgaMetadata->getTableName()} orga ON orga.id = citizenOrga.organization_id
            INNER JOIN {$fleetMetadata->getTableName()} f ON f.id = c.last_fleet_id
            INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id
            WHERE orga.organization_sid = :sid 
        EOT;
        // filtering
        if (count($filter->shipNames) > 0) {
            $sql .= ' AND (';
            foreach ($filter->shipNames as $i => $filterShipName) {
                $sql .= sprintf(' s.name = :shipName_%d OR ', $i);
            }
            $sql .= ' 1=0) ';
        }
        if (count($filter->citizenIds) > 0) {
            $sql .= ' AND (';
            foreach ($filter->citizenIds as $i => $filterCitizenId) {
                $sql .= sprintf(' c.id = :citizenId_%d OR ', $i);
            }
            $sql .= ' 1=0) ';
        }

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Ship::class, 's', ['id' => 'shipId']);
        $rsm->addJoinedEntityFromClassMetadata(Fleet::class, 'f', 's', 'fleet', ['id' => 'fleetId']);
        $rsm->addJoinedEntityFromClassMetadata(Citizen::class, 'c', 'f', 'owner', ['id' => 'citizenId']);

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameter('sid', mb_strtolower($organizationId->getSid()));
        foreach ($filter->shipNames as $i => $filterShipName) {
            $stmt->setParameter('shipName_' . $i, $filterShipName);
        }
        foreach ($filter->citizenIds as $i => $filterCitizenId) {
            $stmt->setParameter('citizenId_' . $i, $filterCitizenId);
        }

        return $stmt->getResult();
    }

    public function countOwnersAndOwnedOfShip(string $organizationId, string $shipName, ShipFamilyFilter $filter): array
    {
        $citizenMetadata = $this->getClassMetadata();
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);

        $sql = <<<EOT
            SELECT count(DISTINCT c.id) as countOwners, count(*) as countOwned FROM {$citizenMetadata->getTableName()} c 
            INNER JOIN {$citizenOrgaMetadata->getTableName()} citizenOrga ON citizenOrga.citizen_id = c.id
            INNER JOIN {$orgaMetadata->getTableName()} orga ON orga.id = citizenOrga.organization_id
            INNER JOIN {$fleetMetadata->getTableName()} f ON f.id = c.last_fleet_id
            INNER JOIN {$shipMetadata->getTableName()} s ON f.id = s.fleet_id and LOWER(s.name) = :shipName 
            WHERE orga.organization_sid = :sid 
        EOT;
        // filtering
        if (count($filter->shipNames) > 0) {
            $sql .= ' AND (';
            foreach ($filter->shipNames as $i => $filterShipName) {
                $sql .= sprintf(' s.name = :shipName_%d OR ', $i);
            }
            $sql .= ' 1=0) ';
        }
        if (count($filter->citizenIds) > 0) {
            $sql .= ' AND (';
            foreach ($filter->citizenIds as $i => $filterCitizenId) {
                $sql .= sprintf(' c.id = :citizenId_%d OR ', $i);
            }
            $sql .= ' 1=0) ';
        }

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('countOwners', 'countOwners');
        $rsm->addScalarResult('countOwned', 'countOwned');
        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'sid' => mb_strtolower($organizationId),
            'shipName' => mb_strtolower($shipName),
        ]);
        foreach ($filter->shipNames as $i => $filterShipName) {
            $stmt->setParameter('shipName_' . $i, $filterShipName);
        }
        foreach ($filter->citizenIds as $i => $filterCitizenId) {
            $stmt->setParameter('citizenId_' . $i, $filterCitizenId);
        }

        return $stmt->getScalarResult();
    }

    /**
     * @param Citizen|null $viewerCitizen the logged citizen
     *
     * @return User[]
     */
    public function getOwnersOfShip(string $organizationId, string $shipName, ?Citizen $viewerCitizen, ShipFamilyFilter $filter, int $page = null, int $itemsPerPage = 10): array
    {
        $userMetadata = $this->_em->getClassMetadata(User::class);
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);

        $sql = <<<EOT
            SELECT u.*, u.id AS userId, 
                   c.*, c.id AS citizenId, 
                   citizenOrga.*, citizenOrga.citizen_id AS citizenOrgaCitizenId, citizenOrga.id AS citizenOrgaId, 
                   COUNT(s.id) as countShips
            FROM {$orgaMetadata->getTableName()} orga
            INNER JOIN {$citizenOrgaMetadata->getTableName()} citizenOrga ON orga.id = citizenOrga.organization_id AND orga.organization_sid = :sid
            INNER JOIN {$citizenMetadata->getTableName()} c ON citizenOrga.citizen_id = c.id
            INNER JOIN {$userMetadata->getTableName()} u ON u.citizen_id = c.id
            INNER JOIN {$fleetMetadata->getTableName()} f ON f.id = c.last_fleet_id
            INNER JOIN {$shipMetadata->getTableName()} s ON s.fleet_id = f.id and LOWER(s.name) = :shipName
            WHERE (
                u.public_choice = :userPublicChoicePublic
                OR (u.public_choice = :userPublicChoiceOrga AND (
                        # visibility ORGA : visible by everyone
                        # visibility ADMIN : visible only by highest orga rank
                        # visibility PRIVATE : visible by anybody
                        citizenOrga.visibility = :visibilityOrga
                        OR (citizenOrga.visibility = :visibilityAdmin AND :viewerCitizenId IS NOT NULL AND :viewerCitizenId IN (
                                # select highest ranks of this orga
                                SELECT co.citizen_id
                                FROM {$orgaMetadata->getTableName()} o
                                INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
                                WHERE co.rank = (
                                    SELECT max(co.rank) AS maxRank
                                    FROM {$orgaMetadata->getTableName()} o
                                    INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
                                )
                            )
                        )
                    )
                )
            )
        EOT;

        // filtering
        if (count($filter->shipNames) > 0) {
            $sql .= ' AND (';
            foreach ($filter->shipNames as $i => $filterShipName) {
                $sql .= sprintf(' s.name = :shipName_%d OR ', $i);
            }
            $sql .= ' 1=0) ';
        }
        if (count($filter->citizenIds) > 0) {
            $sql .= ' AND (';
            foreach ($filter->citizenIds as $i => $filterCitizenId) {
                $sql .= sprintf(' c.id = :citizenId_%d OR ', $i);
            }
            $sql .= ' 1=0) ';
        }
        $sql .= <<<EOT
            GROUP BY u.id, c.id, citizenOrga.id
            ORDER BY countShips DESC
        EOT;
        // pagination
        if ($page !== null) {
            $sql .= "\nLIMIT :first, :countItems\n";
        }

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(User::class, 'u', ['id' => 'userId']);
        $rsm->addJoinedEntityFromClassMetadata(Citizen::class, 'c', 'u', 'citizen', ['id' => 'citizenId']);
        $rsm->addJoinedEntityFromClassMetadata(CitizenOrganization::class, 'citizenOrga', 'c', 'organizations', ['id' => 'citizenOrgaId', 'citizen_id' => 'citizenOrgaCitizenId']);
        $rsm->addScalarResult('countShips', 'countShips');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'sid' => mb_strtolower($organizationId),
            'shipName' => mb_strtolower($shipName),
            'visibilityOrga' => CitizenOrganization::VISIBILITY_ORGA,
            'visibilityAdmin' => CitizenOrganization::VISIBILITY_ADMIN,
            'userPublicChoicePublic' => User::PUBLIC_CHOICE_PUBLIC,
            'userPublicChoiceOrga' => User::PUBLIC_CHOICE_ORGANIZATION,
            'viewerCitizenId' => $viewerCitizen !== null ? $viewerCitizen->getId()->toString() : null,
        ]);
        if ($page !== null) {
            $page = $page < 1 ? 1 : $page;
            $stmt->setParameter('first', ($page - 1) * $itemsPerPage);
            $stmt->setParameter('countItems', $itemsPerPage);
        }
        foreach ($filter->shipNames as $i => $filterShipName) {
            $stmt->setParameter('shipName_' . $i, $filterShipName);
        }
        foreach ($filter->citizenIds as $i => $filterCitizenId) {
            $stmt->setParameter('citizenId_' . $i, $filterCitizenId);
        }

        return $stmt->getResult();
    }

    /**
     * @param Citizen|null $viewerCitizen the logged citizen
     *
     * @return int
     */
    public function countOwnersOfShip(string $organizationId, string $shipName, ?Citizen $viewerCitizen, ShipFamilyFilter $filter): int
    {
        $userMetadata = $this->_em->getClassMetadata(User::class);
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);

        $sql = <<<EOT
            SELECT COUNT(DISTINCT c.id) as total
            FROM {$orgaMetadata->getTableName()} orga
            INNER JOIN {$citizenOrgaMetadata->getTableName()} citizenOrga ON orga.id = citizenOrga.organization_id AND orga.organization_sid = :sid
            INNER JOIN {$citizenMetadata->getTableName()} c ON citizenOrga.citizen_id = c.id
            INNER JOIN {$userMetadata->getTableName()} u ON u.citizen_id = c.id
            INNER JOIN {$fleetMetadata->getTableName()} f ON f.id = c.last_fleet_id
            INNER JOIN {$shipMetadata->getTableName()} s ON s.fleet_id = f.id and LOWER(s.name) = :shipName
            WHERE (
                u.public_choice = :userPublicChoicePublic
                OR (u.public_choice = :userPublicChoiceOrga AND (
                        # visibility ORGA : visible by everyone
                        # visibility ADMIN : visible only by highest orga rank
                        # visibility PRIVATE : visible by anybody
                        citizenOrga.visibility = :visibilityOrga
                        OR (citizenOrga.visibility = :visibilityAdmin AND :viewerCitizenId IS NOT NULL AND :viewerCitizenId IN (
                                # select highest ranks of this orga
                                SELECT co.citizen_id
                                FROM {$orgaMetadata->getTableName()} o
                                INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
                                WHERE co.rank = (
                                    SELECT max(co.rank) AS maxRank
                                    FROM {$orgaMetadata->getTableName()} o
                                    INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
                                )
                            )
                        )
                    )
                )
            )
        EOT;

        // filtering
        if (count($filter->shipNames) > 0) {
            $sql .= ' AND (';
            foreach ($filter->shipNames as $i => $filterShipName) {
                $sql .= sprintf(' s.name = :shipName_%d OR ', $i);
            }
            $sql .= ' 1=0) ';
        }
        if (count($filter->citizenIds) > 0) {
            $sql .= ' AND (';
            foreach ($filter->citizenIds as $i => $filterCitizenId) {
                $sql .= sprintf(' c.id = :citizenId_%d OR ', $i);
            }
            $sql .= ' 1=0) ';
        }

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addScalarResult('total', 'total');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'sid' => mb_strtolower($organizationId),
            'shipName' => mb_strtolower($shipName),
            'visibilityOrga' => CitizenOrganization::VISIBILITY_ORGA,
            'visibilityAdmin' => CitizenOrganization::VISIBILITY_ADMIN,
            'userPublicChoicePublic' => User::PUBLIC_CHOICE_PUBLIC,
            'userPublicChoiceOrga' => User::PUBLIC_CHOICE_ORGANIZATION,
            'viewerCitizenId' => $viewerCitizen !== null ? $viewerCitizen->getId()->toString() : null,
        ]);
        foreach ($filter->shipNames as $i => $filterShipName) {
            $stmt->setParameter('shipName_' . $i, $filterShipName);
        }
        foreach ($filter->citizenIds as $i => $filterCitizenId) {
            $stmt->setParameter('citizenId_' . $i, $filterCitizenId);
        }

        return $stmt->getSingleScalarResult();
    }

    /**
     * @param Citizen|null $viewerCitizen the logged citizen
     *
     * @return int
     */
    public function countHiddenOwnersOfShip(string $organizationId, string $shipName, ?Citizen $viewerCitizen): int
    {
        $userMetadata = $this->_em->getClassMetadata(User::class);
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);
        $fleetMetadata = $this->_em->getClassMetadata(Fleet::class);
        $shipMetadata = $this->_em->getClassMetadata(Ship::class);
        $citizenOrgaMetadata = $this->_em->getClassMetadata(CitizenOrganization::class);
        $orgaMetadata = $this->_em->getClassMetadata(Organization::class);

        $sql = <<<EOT
            SELECT COUNT(DISTINCT c.id) as total
            FROM {$orgaMetadata->getTableName()} orga
            INNER JOIN {$citizenOrgaMetadata->getTableName()} citizenOrga ON orga.id = citizenOrga.organization_id AND orga.organization_sid = :sid
            INNER JOIN {$citizenMetadata->getTableName()} c ON citizenOrga.citizen_id = c.id
            INNER JOIN {$userMetadata->getTableName()} u ON u.citizen_id = c.id
            INNER JOIN {$fleetMetadata->getTableName()} f ON f.id = c.last_fleet_id
            INNER JOIN {$shipMetadata->getTableName()} s ON s.fleet_id = f.id and LOWER(s.name) = :shipName
            # notice the NOT to inverse the normal condition
            WHERE NOT (
                u.public_choice = :userPublicChoicePublic
                OR (u.public_choice = :userPublicChoiceOrga AND (
                        # visibility ORGA : visible by everyone
                        # visibility ADMIN : visible only by highest orga rank
                        # visibility PRIVATE : visible by anybody
                        citizenOrga.visibility = :visibilityOrga
                        OR (citizenOrga.visibility = :visibilityAdmin AND :viewerCitizenId IS NOT NULL AND :viewerCitizenId IN (
                                # select highest ranks of this orga
                                SELECT co.citizen_id
                                FROM {$orgaMetadata->getTableName()} o
                                INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
                                WHERE co.rank = (
                                    SELECT max(co.rank) AS maxRank
                                    FROM {$orgaMetadata->getTableName()} o
                                    INNER JOIN {$citizenOrgaMetadata->getTableName()} co ON co.organization_id = o.id AND o.organization_sid = :sid
                                )
                            )
                        )
                    )
                )
            )
        EOT;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addScalarResult('total', 'total');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameters([
            'sid' => mb_strtolower($organizationId),
            'shipName' => mb_strtolower($shipName),
            'visibilityOrga' => CitizenOrganization::VISIBILITY_ORGA,
            'visibilityAdmin' => CitizenOrganization::VISIBILITY_ADMIN,
            'userPublicChoicePublic' => User::PUBLIC_CHOICE_PUBLIC,
            'userPublicChoiceOrga' => User::PUBLIC_CHOICE_ORGANIZATION,
            'viewerCitizenId' => $viewerCitizen !== null ? $viewerCitizen->getId()->toString() : null,
        ]);

        return $stmt->getSingleScalarResult();
    }
}
