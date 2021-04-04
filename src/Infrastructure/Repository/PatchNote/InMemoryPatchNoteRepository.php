<?php

namespace App\Infrastructure\Repository\PatchNote;

use App\Application\Repository\PatchNoteRepositoryInterface;
use App\Domain\PatchNoteId;
use App\Entity\PatchNote;

class InMemoryPatchNoteRepository implements PatchNoteRepositoryInterface
{
    /** @var PatchNote[] */
    private array $patchNotes = [];

    /**
     * @param PatchNote[] $patchNotes
     */
    public function setPatchNotes(array $patchNotes): void
    {
        $this->patchNotes = [];
        foreach ($patchNotes as $patchNote) {
            $this->patchNotes[(string) $patchNote->getId()] = $patchNote;
        }
    }

    public function getOneRecentPatchNoteId(?\DateTimeInterface $afterDate): ?PatchNoteId
    {
        foreach ($this->patchNotes as $patchNote) {
            if ($afterDate === null || $patchNote->getCreatedAt() > $afterDate) {
                return $patchNote->getId();
            }
        }

        return null;
    }

    public function getLastPatchNotes(int $count): array
    {
        uasort($this->patchNotes, static function (PatchNote $patchNote1, PatchNote $patchNote2): int {
            return $patchNote2->getCreatedAt() <=> $patchNote1->getCreatedAt();
        });

        return array_slice($this->patchNotes, 0, $count);
    }
}
