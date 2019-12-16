<?php

namespace App\Tests\Controller\Organization\Fleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class FleetsControllerTest extends WebTestCase
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
     * @group organization_fleet
     */
    public function testOrgaFleetsPublicNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/gardiens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'chassisId' => '1',
                'name' => 'Aurora',
                'count' => 2,
                'manufacturerCode' => 'RSI',
                'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/store_small/Rsi_aurora_mr_storefront_visual.jpg',
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsPrivateAuth(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'chassisId' => '6',
                'name' => 'Cutlass',
                'count' => 2,
                'manufacturerCode' => 'DRAK',
                'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/store_small/Drake_cutlass_storefront_visual.jpg',
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsPrivateAuthBadOrga(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/not_exist', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('bad_organization', $json['error']);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsPrivateNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsPrivateAuthPrivateOrga(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk', [], [], [
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
    public function testOrgaFleetsPrivateAuthAdminOnlyOrgaFail(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42', [], [], [
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
    public function testOrgaFleetsPrivateAuthAdminOnlyOrgaSuccess(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
