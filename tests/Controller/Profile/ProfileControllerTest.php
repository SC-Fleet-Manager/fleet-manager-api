<?php

namespace App\Tests\Controller\Profile;

use App\Tests\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group profile
     */
    public function testIndex(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertArraySubset([
            'id' => 'd92e229e-e743-4583-905a-e02c57eacfe0',
            'token' => '4682bc58961264de31d38bf6af18cfe717ab2ba59f34b906668b4d7c0ca65b33',
            'createdAt' => '2019-04-02T11:22:33+00:00',
            'nickname' => 'Ioni',
        ], $json);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testIndexNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('forbidden', $json['error']);
    }
}
