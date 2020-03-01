<?php

namespace App\Tests\Controller\Organization\Fleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class FleetAdminsControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsAdmins(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => '3de45df8-3e84-4a69-8e9d-7bff1faa5281',
                'actualHandle' => ['handle' => 'ashuvidz'],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsAdminsNotAuthPublic(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/gardiens/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsAdminsNotPrivateRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsAdminsNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsAdminsAuthAdmin(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => 'f0cdc161-f50c-4aad-b587-ae92d2d4a530',
                'actualHandle' => ['handle' => 'pulsar42_admin'],
            ],
        ], $json);
    }
}
