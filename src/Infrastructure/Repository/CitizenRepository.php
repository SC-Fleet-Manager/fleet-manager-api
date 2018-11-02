<?php

namespace App\Infrastructure\Repository;

use App\Domain\CitizenNumber;
use App\Domain\CitizenRepositoryInterface;
use App\Domain\Fleet;
use App\Domain\HandleSC;
use App\Domain\Money;
use App\Domain\Ship;
use App\Domain\Trigram;
use App\Infrastructure\Entity\Citizen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CitizenRepository extends ServiceEntityRepository implements CitizenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Citizen::class);
    }

    private function createCitizen(Citizen $citizenEntity): \App\Domain\Citizen
    {
        $citizen = new \App\Domain\Citizen($citizenEntity->id);
        $citizen->number = new CitizenNumber($citizenEntity->number);
        $citizen->actualHandle = new HandleSC($citizenEntity->actualHandle);
        foreach ($citizenEntity->organisations as $orga) {
            $citizen->organisations[] = new Trigram($orga);
        }
        foreach ($citizenEntity->fleets as $fleetEntity) {
            $fleet = new Fleet($fleetEntity->id, $citizen);
            $fleet->uploadDate = clone $fleetEntity->uploadDate;
            $fleet->version = $fleetEntity->version;
            foreach ($fleetEntity->ships as $shipEntity) {
                $ship = new Ship($shipEntity->id, $citizen);
                $ship->name = $shipEntity->name;
                $ship->manufacturer = $shipEntity->manufacturer;
                $ship->rawData = $shipEntity->rawData;
                $ship->pledgeDate = clone $shipEntity->pledgeDate;
                $ship->insured = $shipEntity->insured;
                $ship->cost = new Money($shipEntity->cost);
                $fleet->ships[] = $ship;
            }
            $citizen->fleets[] = $fleet;
        }

        return $citizen;
    }

    /**
     * {@inheritdoc}
     */
    public function getByHandle(HandleSC $handle): ?\App\Domain\Citizen
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where('c.actualHandle = :handle')->setParameter('handle', (string) $handle);
        $q = $qb->getQuery();
        $q->useResultCache(true);
        $q->setResultCacheLifetime(3600);
        /** @var Citizen $citizenEntity */
        $citizenEntity = $q->getOneOrNullResult();
        if ($citizenEntity === null) {
            return null;
        }

        return $this->createCitizen($citizenEntity);
    }

    /**
     * {@inheritdoc}
     */
    public function create(\App\Domain\Citizen $citizen): void
    {
        $entity = Citizen::fromCitizen($citizen);

        $em = $this->getEntityManager();
        $em->clear();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(\App\Domain\Citizen $citizen): void
    {
        $entity = Citizen::fromCitizen($citizen);

        $em = $this->getEntityManager();
        $em->clear();
        $em->merge($entity);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getByOrganisation(Trigram $organisationTrigram): iterable
    {
        $qb = $this->createQueryBuilder('c');
        $qb
            ->select('c')
            ->leftJoin('c.fleets', 'f')
            ->addSelect('f')
            ->leftJoin('f.ships', 's')
            ->addSelect('s')
            ->where('c.organisations LIKE :orga')
            ->setParameter('orga', '%'.$organisationTrigram.'%');
        $q = $qb->getQuery();
        $q->useResultCache(true);
        $q->setResultCacheLifetime(900);
        $citizenEntities = $q->getResult();
        $citizens = [];
        foreach ($citizenEntities as $citizenEntity) {
            $citizens[] = $this->createCitizen($citizenEntity);
        }

        return $citizens;
    }
}
