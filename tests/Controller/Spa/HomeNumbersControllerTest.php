<?php

namespace App\Tests\Controller\Spa;

use App\Tests\WebTestCase;

class HomeNumbersControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group api
     */
    public function testNumbers(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/numbers', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'organizations' => 3,
            'users' => 14,
            'ships' => 10,
        ], $json);
    }
}
