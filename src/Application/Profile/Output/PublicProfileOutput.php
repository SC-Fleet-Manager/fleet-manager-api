<?php

namespace App\Application\Profile\Output;

use App\Domain\UserId;

class PublicProfileOutput
{
    public function __construct(
        public UserId $id,
        public ?string $nickname,
        public ?string $handle,
    ) {
    }
}
