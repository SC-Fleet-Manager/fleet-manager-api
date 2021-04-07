<?php

namespace App\Application\Repository;

interface Auth0RepositoryInterface
{
    public function delete(string $auth0Username): void;
}
