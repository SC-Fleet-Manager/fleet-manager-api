<?php

namespace App\Application\Home\Output;

use App\Domain\UserId;
use OpenApi\Annotations as OpenApi;

class MeOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", format="uid", example="00000000-0000-0000-0000-000000000001")
         */
        public UserId $id,
        /**
         * @OpenApi\Property(type="string", format="date-time")
         */
        public \DateTimeInterface $createdAt
    ) {
    }
}
