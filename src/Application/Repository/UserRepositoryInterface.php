<?php

namespace App\Application\Repository;

use App\Domain\UserId;
use App\Entity\User;

interface UserRepositoryInterface
{
    public function countUsers(): int;

    public function findByAuth0Username(string $username): ?User;

    public function getById(UserId $userId): ?User;

    /**
     * @param UserId[] $userIds
     *
     * @return User[]
     */
    public function getByIds(array $userIds): array;

    public function save(User $user): void;

    public function delete(User $user): void;
}
