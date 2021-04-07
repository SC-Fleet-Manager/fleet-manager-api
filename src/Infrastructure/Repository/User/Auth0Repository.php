<?php

namespace App\Infrastructure\Repository\User;

use App\Application\Repository\Auth0RepositoryInterface;
use Auth0\SDK\API\Management;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class Auth0Repository implements Auth0RepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private Management $managementApi
    ) {
    }

    public function delete(string $auth0Username): void
    {
        try {
            $this->managementApi->users()->delete($auth0Username);
        } catch (\Throwable $e) {
            $this->logger->error('Unable to delete user on Auth0 : '.$e->getMessage(), ['exception' => $e, 'username' => $auth0Username]);

            throw $e;
        }
    }
}
