<?php

namespace App\Service\Funding;

use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPalCheckoutSdk\Core\PayPalEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class PayPalHttpClientFactory
{
    public const MODE_PROD = 'prod';
    public const MODE_SANDBOX = 'sandbox';

    private string $mode;
    private string $clientId;
    private string $clientSecret;

    public function __construct(string $clientId, string $clientSecret, $mode = self::MODE_SANDBOX)
    {
        $this->mode = $mode;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    public function createPayPalHttpClient(): PayPalHttpClient
    {
        return new PayPalHttpClient($this->createEnvironment());
    }

    public function createApiContext(): ApiContext
    {
        $apiContext = new ApiContext(new OAuthTokenCredential($this->clientId, $this->clientSecret));
        $apiContext->setConfig([
            'mode' => $this->mode === self::MODE_PROD ? 'live' : 'sandbox',
        ]);

        return $apiContext;
    }

    private function createEnvironment(): PayPalEnvironment
    {
        if ($this->mode === self::MODE_PROD) {
            return new ProductionEnvironment($this->clientId, $this->clientSecret);
        }

        return new SandboxEnvironment($this->clientId, $this->clientSecret);
    }
}
