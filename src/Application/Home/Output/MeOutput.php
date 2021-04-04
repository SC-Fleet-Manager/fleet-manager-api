<?php

namespace App\Application\Home\Output;

use App\Domain\UserId;

class MeOutput
{
    public function __construct(
        public UserId $id,
        public \DateTimeInterface $createdAt
    ) {
    }
}
