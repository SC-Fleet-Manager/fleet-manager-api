<?php

namespace App\Application\PatchNote\Output;

use App\Domain\PatchNoteId;

class LastPatchNoteOutput
{
    public function __construct(
        public PatchNoteId $id,
        public string $title,
        public string $body,
        public ?string $link,
        public \DateTimeInterface $createdAt,
    ) {
    }
}
