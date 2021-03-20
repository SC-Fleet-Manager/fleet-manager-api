<?php

namespace App\Application\PatchNote\Output;

class HasNewPatchNoteOutput
{
    public function __construct(
        public bool $hasNewPatchNote,
    ) {
    }
}
