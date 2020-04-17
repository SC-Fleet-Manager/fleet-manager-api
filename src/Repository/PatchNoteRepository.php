<?php

namespace App\Repository;

use App\Entity\PatchNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PatchNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PatchNote::class);
    }

    public function findOneRecentPatchNote(?\DateTimeInterface $afterDate): ?PatchNote
    {
        $qb = $this->createQueryBuilder('patch_note');
        $qb->setMaxResults(1);
        if ($afterDate !== null) {
            $qb->where('patch_note.createdAt > :afterDate');
            $qb->setParameter('afterDate', $afterDate);
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
