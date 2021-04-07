<?php

namespace App\Domain\Event;

use App\Domain\UserId;

class DeletedUser
{
    public function __construct(
        private UserId $userId,
        private string $auth0Username,
    ) {
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getAuth0Username(): string
    {
        return $this->auth0Username;
    }
}
