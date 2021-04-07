<?php

namespace App\Infrastructure\Repository\User;

use App\Application\Repository\Auth0RepositoryInterface;

class FakeAuth0Repository implements Auth0RepositoryInterface
{
    /** @var string[] */
    private array $deletedUsers = [];

    public function delete(string $auth0Username): void
    {
        $this->deletedUsers[] = $auth0Username;
    }

    /**
     * @return string[]
     */
    public function getDeletedUsers(): array
    {
        return $this->deletedUsers;
    }
}
