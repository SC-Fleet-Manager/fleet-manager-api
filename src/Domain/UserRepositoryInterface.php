<?php

namespace App\Domain;

interface UserRepositoryInterface
{
    public function getByUsername(string $username): ?User;

    public function create(User $user): void;
}
