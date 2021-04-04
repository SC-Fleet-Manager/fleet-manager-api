<?php

namespace App\Application\Profile\Output;

use App\Domain\UserId;

class ProfileOutput
{
    public function __construct(
        public UserId $id,
        public string $auth0Username,
        public ?string $nickname,
        public ?string $pictureUrl,
        public bool $supporterVisible,
        public int $coins,
        public \DateTimeInterface $createdAt,
    ) {
    }
}
