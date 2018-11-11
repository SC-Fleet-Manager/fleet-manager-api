<?php

namespace App\Infrastructure\Repository;

use App\Domain\Fleet as DomainFleet;
use App\Domain\FleetRepositoryInterface;
use App\Infrastructure\Entity\Fleet;
use App\Infrastructure\Repository\Serializer\FleetSerializer;
use App\Infrastructure\Repository\Serializer\ShipSerializer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class FleetRepository extends ServiceEntityRepository implements FleetRepositoryInterface
{
    /**
     * @var FleetSerializer
     */
    private $fleetSerializer;

    /**
     * @var ShipSerializer
     */
    private $shipSerializer;

    public function __construct(ManagerRegistry $registry, FleetSerializer $fleetSerializer, ShipSerializer $shipSerializer)
    {
        parent::__construct($registry, Fleet::class);
        $this->fleetSerializer = $fleetSerializer;
        $this->shipSerializer = $shipSerializer;
    }

    public function save(DomainFleet $fleet): void
    {
        $em = $this->getEntityManager();
        $em->clear();

        $fleetEntity = $this->fleetSerializer->fromDomain($fleet);
        $fleetEntity->owner = $em->merge($fleetEntity->owner);
        foreach ($fleet->ships as $ship) {
            $shipEntity = $this->shipSerializer->fromDomain($ship);
            $shipEntity->fleet = $fleetEntity;
            $shipEntity->owner = $em->merge($shipEntity->owner);
            $em->persist($shipEntity);
        }

        $em->persist($fleetEntity);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getLastVersionFleet(\App\Domain\Citizen $citizen): ?DomainFleet
    {
        $qb = $this->createQueryBuilder('f');
        $qb
            ->select('f')
            ->join('f.owner', 'o')
            ->addSelect('o')
            ->leftJoin('f.ships', 's')
            ->addSelect('s')
            ->where('f.owner = :owner')
            ->orderBy('f.version', 'DESC')
            ->setParameter('owner', $citizen->id)
            ->setMaxResults(1);
        $q = $qb->getQuery();
//        $q->useResultCache(true);
//        $q->setResultCacheLifetime(300);
        $fleetEntity = $q->getOneOrNullResult();
        if ($fleetEntity === null) {
            return null;
        }

        return $this->fleetSerializer->toDomain($fleetEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): iterable
    {
        $fleetsEntity = $this->findAll();
        $fleets = [];
        foreach ($fleetsEntity as $fleetEntity) {
            $fleets[] = $this->fleetSerializer->toDomain($fleetEntity);
        }

        return $fleets;
    }
}
