<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\WebTestCase;

class FleetControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']);
    }

    public function testMyFleetNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    public function testMyFleet(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'fleet' => [
                'id' => '8fb44dad-8d40-447e-a670-62b3192d5521',
                'version' => 1,
                'uploadDate' => '2019-04-05T00:00:00+00:00',
                'ships' => [
                    [
                        'id' => 'a75256db-07fa-4f49-95f9-9f44bd7fbd72',
                        'name' => 'Cutlass Black',
                        'manufacturer' => 'Drake',
                        'pledgeDate' => '2019-04-10T00:00:00+00:00',
                        'cost' => 110,
                        'insured' => true,
                    ]
                ]
            ]
        ], $json);
        $this->assertArrayHasKey('shipInfos', $json);
    }

    public function testUserFleetPublic(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/user-fleet/ionni', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserFleetPrivate(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/user-fleet/ashuvidz', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());

        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_rights', $json['error']);
    }
}
