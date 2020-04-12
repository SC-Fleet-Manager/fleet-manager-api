<?php

namespace App\Tests\Controller\Organization;

use App\Entity\User;
use App\Tests\WebTestCase;

class ShipsControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group organization
     */
    public function testShips(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => 'cbcb60c7-a780-4a59-b51d-0ad8021813bf',
                'label' => 'Aurora MR',
            ],
            [
                'id' => 'f43fa89e-d34f-43d2-807d-5e8bf8c8929a',
                'label' => 'Constellation Andromeda',
            ],
            [
                'id' => 'e37c618b-3ec6-4d4d-92b6-5aed679962a2',
                'label' => 'Cutlass Black',
            ],
            [
                'id' => '05e980c5-6425-4fe4-a3c2-d69a0d568e40',
                'label' => 'Dragonfly Black',
            ],
            [
                'id' => '9950adb5-9151-4760-9073-080416120fca',
                'label' => 'Orion',
            ],
            [
                'id' => 'f250a2b7-76ea-481f-84b5-3e2e96d40e84',
                'label' => 'Ranger CV',
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testShipsNotPublicRights(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testShipsNotPrivateRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testShipsNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/pulsar42/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testShipsAdmin(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/pulsar42/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
