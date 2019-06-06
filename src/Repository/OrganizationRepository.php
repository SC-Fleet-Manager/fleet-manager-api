<?php

namespace App\Repository;

use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class OrganizationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function updatePublicChoice(string $sid, string $publicChoice): void
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->update('App:Organization', 'orga')
            ->set('orga.publicChoice', ':publicChoice')
            ->where('orga.organizationSid = :sid')
            ->setParameters([
                'sid' => $sid,
                'publicChoice' => $publicChoice,
            ])
            ->getQuery()
            ->execute();
    }
}
