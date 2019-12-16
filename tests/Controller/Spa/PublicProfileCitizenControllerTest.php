<?php

namespace App\Tests\Controller\Spa;

use App\Tests\WebTestCase;

class PublicProfileCitizenControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group api
     */
    public function testOrganization(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/citizen/ionni', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'nickname' => 'Ioni14',
            'actualHandle' => [
                'handle' => 'ionni',
            ],
            'avatarUrl' => null,
        ], $json);
    }

    /**
     * @group functional
     * @group api
     */
    public function testOrganizationNotExist(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/citizen/not_exist', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('citizen_not_found', $json['error']);
    }
}
