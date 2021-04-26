<?php

namespace App\Application\PatchNote;

use App\Application\PatchNote\Output\LastPatchNoteOutput;
use App\Application\PatchNote\Output\LastPatchNotesOutput;
use App\Application\Repository\PatchNoteRepositoryInterface;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;
use App\Entity\PatchNote;

class LastPatchNotesService
{
    private const COUNT_PATCH_NOTES = 10;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PatchNoteRepositoryInterface $patchNoteRepository,
    ) {
    }

    /**
     * @param UserId|null $userId its last patch note date will be updated
     */
    public function handle(?UserId $userId = null): LastPatchNotesOutput
    {
        $patchNotes = $this->patchNoteRepository->getLastPatchNotes(self::COUNT_PATCH_NOTES);

        if ($userId !== null && count($patchNotes) > 0) {
            $this->updateLastPatchNoteDate($userId, reset($patchNotes));
        }

        return $this->createOutput($patchNotes);
    }

    private function updateLastPatchNoteDate(UserId $userId, PatchNote $lastPatchNote): void
    {
        $user = $this->userRepository->getById($userId);
        if ($user === null) {
            return;
        }
        if ($user->hasReadPatchNote($lastPatchNote)) {
            return;
        }
        $user->readPatchNote($lastPatchNote);
        $this->userRepository->save($user);
    }

    private function createOutput(array $patchNotes): LastPatchNotesOutput
    {
        $patchNotesOutput = [];
        foreach ($patchNotes as $patchNote) {
            $patchNotesOutput[] = new LastPatchNoteOutput(
                $patchNote->getId(),
                $patchNote->getTitle(),
                $patchNote->getBody(),
                $patchNote->getLink(),
                $patchNote->getCreatedAt(),
            );
        }

        return new LastPatchNotesOutput($patchNotesOutput);
    }
}
