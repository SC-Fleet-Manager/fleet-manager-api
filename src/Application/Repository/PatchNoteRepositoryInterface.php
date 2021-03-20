<?php

namespace App\Application\Repository;

use App\Domain\PatchNoteId;
use App\Domain\UserId;

interface PatchNoteRepositoryInterface
{
    public function getOneRecentPatchNoteId(?\DateTimeInterface $afterDate): ?PatchNoteId;
}
