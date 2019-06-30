<?php

namespace App\Repository;

use App\Domain\SpectrumIdentification;
use App\Entity\Ship;
use Doctrine\ORM\EntityManager;

/**
 * @property EntityManager $_em
 */
trait OrganizationStatisticsRepositoryTrait
{
    public function statTotalShipsByOrga(SpectrumIdentification $sid): ?int
    {
        $dql = '
            SELECT COUNT(ship.id) AS countShips FROM App\Entity\Organization orga
            INNER JOIN App\Entity\CitizenOrganization citizenOrga WITH citizenOrga.organization = orga.id AND orga.organizationSid = :sid
            INNER JOIN citizenOrga.citizen citizen
            INNER JOIN citizen.lastFleet fleet
            LEFT JOIN fleet.ships ship
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sid', mb_strtolower($sid->getSid()));
        $query->useResultCache(true);
        $query->setResultCacheLifetime(300);

        return $query->getSingleScalarResult();
    }

    /**
     * @return Ship[]
     */
    public function statShipsByOrga(SpectrumIdentification $sid): array
    {
        $dql = '
            SELECT ship FROM App\Entity\Organization orga
            INNER JOIN App\Entity\CitizenOrganization citizenOrga WITH citizenOrga.organization = orga.id AND orga.organizationSid = :sid
            INNER JOIN citizenOrga.citizen citizen
            INNER JOIN citizen.lastFleet fleet
            LEFT JOIN App\Entity\Ship ship WITH ship.fleet = fleet.id
        ';
        $query = $this->_em->createQuery($dql);
        $query->setParameter('sid', mb_strtolower($sid->getSid()));
        $query->useResultCache(true);
        $query->setResultCacheLifetime(300);

        return $query->getResult();
    }
}
