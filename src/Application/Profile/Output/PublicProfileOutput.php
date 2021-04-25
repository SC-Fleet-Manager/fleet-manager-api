<?php

namespace App\Application\Profile\Output;

use App\Domain\UserId;
use OpenApi\Annotations as OpenApi;

class PublicProfileOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", format="uid", example="00000000-0000-0000-0000-000000000001")
         */
        public UserId $id,
        /**
         * @OpenApi\Property(type="string", nullable=true, example="Ioni")
         */
        public ?string $nickname,
    ) {
    }
}
