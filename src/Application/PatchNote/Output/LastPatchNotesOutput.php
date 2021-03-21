<?php

namespace App\Application\PatchNote\Output;

use Webmozart\Assert\Assert;

class LastPatchNotesOutput
{
    public function __construct(
        /** @var LastPatchNoteOutput[] */
        public array $patchNotes
    ) {
        Assert::allIsInstanceOf($this->patchNotes, LastPatchNoteOutput::class);
    }
}
