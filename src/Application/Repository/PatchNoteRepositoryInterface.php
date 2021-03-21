<?php

namespace App\Application\Repository;

use App\Domain\PatchNoteId;
use App\Entity\PatchNote;

interface PatchNoteRepositoryInterface
{
    public function getOneRecentPatchNoteId(?\DateTimeInterface $afterDate): ?PatchNoteId;

    /**
     * @param int $count max patch notes returned
     *
     * @return PatchNote[]
     */
    public function getLastPatchNotes(int $count): array;
}
