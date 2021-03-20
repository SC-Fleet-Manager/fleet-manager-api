<?php

namespace App\Application\Repository;

use App\Domain\UserId;
use App\Entity\User;

interface UserRepositoryInterface
{
    public function countUsers(): int;

    public function findByAuth0Username(string $username): ?User;

    public function getById(UserId $userId): ?User;

    public function save(User $user): void;
}
