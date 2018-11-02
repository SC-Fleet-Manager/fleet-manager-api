<?php

namespace App\Infrastructure\Repository;

use App\Domain\FleetRepositoryInterface;
use App\Infrastructure\Entity\Fleet;
use App\Infrastructure\Entity\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;

class FleetRepository extends ServiceEntityRepository implements FleetRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fleet::class);
    }

    public function save(\App\Domain\Fleet $fleet): void
    {
        $em = $this->getEntityManager();
        $em->clear();

        $fleetEntity = Fleet::fromFleet($fleet);
        $fleetEntity->owner = $em->merge($fleetEntity->owner);
        foreach ($fleet->ships as $ship) {
            $shipEntity = Ship::fromShip($ship);
            $shipEntity->fleet = $fleetEntity;
            $shipEntity->owner = $em->merge($shipEntity->owner);
            $em->persist($shipEntity);
        }

        $em->persist($fleetEntity);
        $em->flush();
    }

    public function getLastVersionFleet(\App\Domain\Citizen $citizen): int
    {
        $qb = $this->createQueryBuilder('f');
        $qb
            ->select('f.version')
            ->where('f.owner = :owner')
            ->orderBy('f.version', 'DESC')
            ->setParameter('owner', $citizen->id)
            ->setMaxResults(1);
        $q = $qb->getQuery();
        $q->useResultCache(true);
        $q->setResultCacheLifetime(3600);
        $lastVersion = $q->getOneOrNullResult(AbstractQuery::HYDRATE_SINGLE_SCALAR) ?? 0;

        return $lastVersion;
    }
}
