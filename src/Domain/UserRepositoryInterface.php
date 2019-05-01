<?php

namespace App\Domain;

interface UserRepositoryInterface
{
    public function getByUsername(string $username): ?User;

    public function getByDiscordId(string $id): ?User;

    public function create(User $user): void;

    public function update(User $user): void;
}
