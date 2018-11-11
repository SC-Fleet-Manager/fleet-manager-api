<?php

namespace App\Infrastructure\Repository;

use App\Domain\Citizen as DomainCitizen;
use App\Domain\CitizenRepositoryInterface;
use App\Domain\Fleet as DomainFleet;
use App\Domain\HandleSC;
use App\Domain\Trigram;
use App\Infrastructure\Entity\Citizen;
use App\Infrastructure\Repository\Serializer\CitizenSerializer;
use App\Infrastructure\Repository\Serializer\FleetSerializer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CitizenRepository extends ServiceEntityRepository implements CitizenRepositoryInterface
{
    /**
     * @var CitizenSerializer
     */
    private $citizenSerializer;

    /**
     * @var FleetSerializer
     */
    private $fleetSerializer;

    public function __construct(ManagerRegistry $registry, CitizenSerializer $citizenSerializer, FleetSerializer $fleetSerializer)
    {
        parent::__construct($registry, Citizen::class);
        $this->citizenSerializer = $citizenSerializer;
        $this->fleetSerializer = $fleetSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getByHandle(HandleSC $handle): ?DomainCitizen
    {
        $qb = $this->createQueryBuilder('c');
        $qb->where('c.actualHandle = :handle')->setParameter('handle', (string) $handle);
        $q = $qb->getQuery();
//        $q->useResultCache(true);
//        $q->setResultCacheLifetime(900);
        /** @var Citizen $citizenEntity */
        $citizenEntity = $q->getOneOrNullResult();
        if ($citizenEntity === null) {
            return null;
        }

        $citizen = $this->citizenSerializer->toDomain($citizenEntity);
        foreach ($citizenEntity->fleets as $fleetEntity) {
            $fleet = new DomainFleet($fleetEntity->id, $citizen);
            $this->fleetSerializer->toDomain($fleetEntity, $fleet);
            $citizen->fleets[] = $fleet;
        }

        return $citizen;
    }

    /**
     * {@inheritdoc}
     */
    public function create(DomainCitizen $citizen): void
    {
        $entity = $this->citizenSerializer->fromDomain($citizen);

        $em = $this->getEntityManager();
        $em->clear();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(DomainCitizen $citizen): void
    {
        $entity = $this->citizenSerializer->fromDomain($citizen);

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
        $q->setResultCacheLifetime(30);
        $citizenEntities = $q->getResult();
        $citizens = [];
        foreach ($citizenEntities as $citizenEntity) {
            $citizen = $this->citizenSerializer->toDomain($citizenEntity);
            foreach ($citizenEntity->fleets as $fleetEntity) {
                $fleet = new DomainFleet($fleetEntity->id, $citizen);
                $this->fleetSerializer->toDomain($fleetEntity, $fleet);
                $citizen->fleets[] = $fleet;
            }
            $citizens[] = $citizen;
        }

        return $citizens;
    }
}
