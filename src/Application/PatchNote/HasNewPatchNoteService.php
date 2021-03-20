<?php

namespace App\Application\PatchNote;

use App\Application\Exception\NotFoundUserException;
use App\Application\PatchNote\Output\HasNewPatchNoteOutput;
use App\Application\Repository\PatchNoteRepositoryInterface;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;

class HasNewPatchNoteService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PatchNoteRepositoryInterface $patchNoteRepository,
    ) {
    }

    public function handle(UserId $userId): HasNewPatchNoteOutput
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            throw new NotFoundUserException($userId);
        }

        $patchNoteId = $this->patchNoteRepository->getOneRecentPatchNoteId($user->getLastPatchNoteReadAt());

        return new HasNewPatchNoteOutput($patchNoteId !== null);
    }
}
