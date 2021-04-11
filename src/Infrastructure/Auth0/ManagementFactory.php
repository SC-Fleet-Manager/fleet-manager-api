<?php

namespace App\Infrastructure\Auth0;

use Auth0\SDK\API\Authentication;
use Auth0\SDK\API\Management;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Contracts\Cache\CacheInterface;

class ManagementFactory implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private Authentication $authentication,
        private CacheInterface $cache,
        private string $domain,
        private string $grantType,
        private string $scope,
        private string $audience,
    ) {
    }

    public function create(): Management
    {
        $accessToken = $this->cache->get('app.auth0.management.access_token', function (CacheItemInterface $item) {
            try {
                $response = $this->authentication->oauth_token([
                    'grant_type' => $this->grantType,
                    'scope' => $this->scope,
                    'audience' => $this->audience,
                ]);
            } catch (\Throwable $e) {
                $this->logger->error('Unable to request an access token on Auth0 : '.$e->getMessage(), ['exception' => $e]);

                throw $e;
            }
            $item->expiresAfter($response['expires_in']);

            return $response['access_token'];
        }, 0 /* no early expiration */);

        return new Management(
            $accessToken,
            $this->domain,
        );
    }
}
