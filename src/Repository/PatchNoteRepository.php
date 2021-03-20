<?php

namespace App\Repository;

use App\Domain\PatchNoteId;
use App\Entity\PatchNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class PatchNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PatchNote::class);
    }

    public function findOneRecentPatchNoteId(?\DateTimeInterface $afterDate): ?PatchNoteId
    {
        $qb = $this->createQueryBuilder('patch_note');
        $qb->select('patch_note.id');
        $qb->setMaxResults(1);
        if ($afterDate !== null) {
            $qb->where('patch_note.createdAt > :afterDate');
            $qb->setParameter('afterDate', $afterDate);
        }
        $result = $qb->getQuery()->getOneOrNullResult();

        return isset($result['id']) ? new PatchNoteId($result['id']) : null;
    }
}
