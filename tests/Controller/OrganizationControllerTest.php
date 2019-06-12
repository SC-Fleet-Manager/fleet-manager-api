<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\WebTestCase;

class OrganizationControllerTest extends WebTestCase
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
     * @group orga
     */
    public function testSavePreferences(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ashuvidz']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'publicChoice' => 'private',
        ]));

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group orga
     */
    public function testSavePreferencesNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group orga
     */
    public function testSavePreferencesNotAdmin(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'publicChoice' => 'private',
        ]));

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights', $json['error']);
    }

    /**
     * @group functional
     * @group orga
     */
    public function testCitizens(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => '08cc11ec-26ac-4638-8e03-c40b857d32bd',
                'label' => 'ihaveships',
            ],
            [
                'id' => '7275c744-6a69-43c2-9ebf-1491a104d5e7',
                'label' => 'ionni',
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group orga
     */
    public function testCitizensNotPublicRights(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }

    /**
     * @group functional
     * @group orga
     */
    public function testCitizensNotPrivateRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group orga
     */
    public function testCitizensNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/pulsar42/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group orga
     */
    public function testCitizensAdmin(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/pulsar42/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group orga
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
     * @group orga
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
     * @group orga
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
     * @group orga
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
     * @group orga
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
