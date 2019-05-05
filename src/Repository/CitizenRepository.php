<?php

namespace App\Repository;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CitizenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Citizen::class);
    }

    /**
     * @return iterable|Citizen[]
     */
    public function getByOrganisation(SpectrumIdentification $organisationTrigram): iterable
    {
        $q = $this->createQueryBuilder('c')
            ->select('c')
            ->leftJoin('c.fleets', 'f')
            ->addSelect('f')
            ->leftJoin('f.ships', 's')
            ->addSelect('s')
            ->where('c.organisations LIKE :orga')
            ->setParameter('orga', '%'.$organisationTrigram.'%')
            ->getQuery();
        $q->useResultCache(true);
        $q->setResultCacheLifetime(30);

        return $q->getResult();
    }
}
