<?php

namespace App\Domain;

class MemberProfile
{
    public function __construct(
        private MemberId $id,
        private ?string $nickname,
    ) {
    }

    public function getId(): MemberId
    {
        return $this->id;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }
}
