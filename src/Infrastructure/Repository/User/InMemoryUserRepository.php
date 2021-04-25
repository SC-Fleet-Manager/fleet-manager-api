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

    /**
     * {@inheritDoc}
     */
    public function getByIds(array $userIds): array
    {
        $result = [];
        foreach ($userIds as $userId) {
            if (isset($this->usersById[(string) $userId])) {
                $result[] = $this->usersById[(string) $userId];
            }
        }

        return $result;
    }

    public function save(User $user): void
    {
        $this->usersById[(string) $user->getId()] = $user;
    }

    public function delete(User $user): void
    {
        unset($this->usersById[(string) $user->getId()]);
    }

    public function getByHandle(string $handle): ?User
    {
        foreach ($this->usersById as $user) {
            if ($user->getHandle() === $handle) {
                return $user;
            }
        }

        return null;
    }
}
