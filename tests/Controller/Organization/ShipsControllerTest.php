<?php

namespace App\Tests\Controller\Organization;

use App\Entity\User;
use App\Tests\WebTestCase;

class ShipsControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testShips(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => 'Aurora MR',
                'label' => 'Aurora MR',
            ],
            [
                'id' => 'Constellation Andromeda',
                'label' => 'Constellation Andromeda',
            ],
            [
                'id' => 'Cutlass Black',
                'label' => 'Cutlass Black',
            ],
            [
                'id' => 'Dragonfly Black',
                'label' => 'Dragonfly Black',
            ],
            [
                'id' => 'Orion',
                'label' => 'Orion',
            ],
            [
                'id' => 'Ranger CV',
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
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testShipsNotPrivateRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testShipsNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/pulsar42/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testShipsAdmin(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/pulsar42/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
