<?php

namespace App\Infrastructure\Repository\User;

use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;
use App\Entity\User;

class InMemoryUserRepository implements UserRepositoryInterface
{
    /** @var User[] */
    private array $usersByAuth0Username = [];
    /** @var User[] */
    private array $usersById = [];

    /**
     * @param User[] $users
     */
    public function setUsers(array $users): void
    {
        $this->usersByAuth0Username = [];
        foreach ($users as $user) {
            $this->usersByAuth0Username[$user->getAuth0Username()] = $user;
            $this->usersById[(string) $user->getId()] = $user;
        }
    }

    public function countUsers(): int
    {
        return count($this->usersByAuth0Username);
    }

    public function findByAuth0Username(string $username): ?User
    {
        return $this->usersByAuth0Username[$username] ?? null;
    }

    public function getById(UserId $userId): ?User
    {
        return $this->usersById[(string) $userId] ?? null;
    }

    public function save(User $user): void
    {
        $this->usersById[(string) $user->getId()] = $user;
    }
}
