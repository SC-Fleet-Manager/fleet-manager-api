<?php

namespace App\Tests\Controller\Funding;

use App\Tests\WebTestCase;

class MyBackingsControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     */
    public function test_index(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/funding/my-backings', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertArraySubset([
            [
                'id' => '1154f530-dbb5-425d-94e8-9a3200b75e35',
                'gateway' => 'paypal',
                'paypalStatus' => 'COMPLETED',
                'amount' => 5133,
                'netAmount' => 4983,
                'currency' => 'USD',
                'refundedAmount' => 0,
                'refundedNetAmount' => 0,
                'refundedAt' => null,
                'effectiveAmount' => 5133,
            ],
            [
                'id' => '618c4d07-6e1d-49e3-91e9-d269944de266',
                'gateway' => 'paypal',
                'paypalStatus' => 'CREATED',
                'amount' => 100,
                'netAmount' => 0,
                'currency' => 'USD',
                'refundedAmount' => 0,
                'refundedNetAmount' => 0,
                'refundedAt' => null,
                'effectiveAmount' => 100,
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group funding
     */
    public function test_not_auth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/funding/my-backings', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('forbidden', $json['error']);
    }
}
