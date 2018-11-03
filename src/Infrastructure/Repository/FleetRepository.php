<?php

namespace App\Infrastructure\Repository;

use App\Domain\CitizenNumber;
use App\Domain\FleetRepositoryInterface;
use App\Domain\HandleSC;
use App\Domain\Money;
use App\Domain\Trigram;
use App\Infrastructure\Entity\Fleet;
use App\Infrastructure\Entity\Ship;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class FleetRepository extends ServiceEntityRepository implements FleetRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fleet::class);
    }

    private function createFleet(Fleet $fleetEntity): \App\Domain\Fleet
    {
        $citizenEntity = $fleetEntity->owner;
        $citizen = new \App\Domain\Citizen($citizenEntity->id);
        $citizen->number = new CitizenNumber($citizenEntity->number);
        $citizen->actualHandle = new HandleSC($citizenEntity->actualHandle);
        foreach ($citizenEntity->organisations as $orga) {
            $citizen->organisations[] = new Trigram($orga);
        }

        $fleet = new \App\Domain\Fleet($fleetEntity->id, $citizen);
        foreach ($fleetEntity->ships as $shipEntity) {
            $ship = new \App\Domain\Ship($shipEntity->id, $citizen);
            $ship->name = $shipEntity->name;
            $ship->cost = new Money($shipEntity->cost);
            $ship->insured = $shipEntity->insured;
            $ship->manufacturer = $shipEntity->manufacturer;
            $ship->pledgeDate = clone $shipEntity->pledgeDate;
            $ship->rawData = $shipEntity->rawData;
            $fleet->ships[] = $ship;
        }
        $fleet->uploadDate = clone $fleetEntity->uploadDate;
        $fleet->version = $fleetEntity->version;

        return $fleet;
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

    public function getLastVersionFleet(\App\Domain\Citizen $citizen): ?\App\Domain\Fleet
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
        $q->useResultCache(true);
        $q->setResultCacheLifetime(3600);
        $fleet = $q->getOneOrNullResult();
        if ($fleet === null) {
            return null;
        }

        return $this->createFleet($fleet);
    }

    /**
     * {@inheritdoc}
     */
    public function all(): iterable
    {
        $fleetsEntity = $this->findAll();
        $fleets = [];
        foreach ($fleetsEntity as $fleetEntity) {
            $fleets[] = $this->createFleet($fleetEntity);
        }

        return $fleets;
    }
}
