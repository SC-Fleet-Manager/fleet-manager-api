<?php

namespace App\Domain;

class MemberProfile
{
    public function __construct(
        private MemberId $id,
        private ?string $nickname,
        private ?string $handle,
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

    public function getHandle(): ?string
    {
        return $this->handle;
    }
}
