<?php

namespace App\Application\PatchNote\Output;

use App\Domain\PatchNoteId;
use OpenApi\Annotations as OpenApi;

class LastPatchNoteOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", format="uid", example="00000000-0000-0000-0000-000000000001")
         */
        public PatchNoteId $id,
        /**
         * @OpenApi\Property(type="string", example="A new feature has been released!")
         */
        public string $title,
        /**
         * @OpenApi\Property(type="string", example="Here the description in html of the <strong>new feature</strong>.")
         */
        public string $body,
        /**
         * @OpenApi\Property(type="string", format="url", nullable=true, example="https://blog.fleet-manager.space/new-feature-released")
         */
        public ?string $link,
        /**
         * @OpenApi\Property(type="string", format="date-time")
         */
        public \DateTimeInterface $createdAt,
    ) {
    }
}
