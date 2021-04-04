<?php

namespace App\Domain;

class UserProfile
{
    public function __construct(
        private ?string $nickname = null,
        private ?string $pictureUrl = null,
        private ?string $locale = null,
        private ?string $email = null,
    ) {
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
