<?php

namespace App\Infrastructure\Repository;

use App\Domain\CitizenNumber;
use App\Domain\CitizenRepositoryInterface;
use App\Domain\HandleSC;
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

        return $citizen;
    }

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

    public function create(\App\Domain\Citizen $citizen): void
    {
        $entity = Citizen::fromCitizen($citizen);

        $em = $this->getEntityManager();
        $em->clear();
        $em->persist($entity);
        $em->flush();
    }
}
