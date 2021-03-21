<?php

namespace App\Infrastructure\Repository\PatchNote;

use App\Application\Repository\PatchNoteRepositoryInterface;
use App\Domain\PatchNoteId;
use App\Entity\PatchNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DoctrinePatchNoteRepository extends ServiceEntityRepository implements PatchNoteRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PatchNote::class);
    }

    public function getOneRecentPatchNoteId(?\DateTimeInterface $afterDate): ?PatchNoteId
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

    public function getLastPatchNotes(int $count): array
    {
        return $this->createQueryBuilder('patch_note')
            ->orderBy('patch_note.createdAt', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult();
    }
}
