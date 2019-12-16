<?php

namespace App\Tests\Controller\Spa;

use App\Tests\WebTestCase;

class OrganizationControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group api
     */
    public function testOrganization(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'id' => '80db0703-dd43-49a0-93d3-89947b9ab321',
            'organizationSid' => 'flk',
            'name' => 'FallKrom',
            'avatarUrl' => null,
            'publicChoice' => 'private',
        ], $json);
    }

    /**
     * @group functional
     * @group api
     */
    public function testOrganizationNotExist(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/not_exist', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('orga_not_exist', $json['error']);
    }
}
