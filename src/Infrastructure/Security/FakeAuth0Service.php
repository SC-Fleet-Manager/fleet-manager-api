<?php

namespace App\Infrastructure\Security;

use Auth0\JWTAuthBundle\Security\Auth0Service;

class FakeAuth0Service extends Auth0Service
{
    private array $profile = [];

    public function setUserProfile(array $profile): void
    {
        $this->profile = $profile;
    }

    public function getUserProfileByA0UID(string $jwt): ?array
    {
        return $this->profile;
    }
}
