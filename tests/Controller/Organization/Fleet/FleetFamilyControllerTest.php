<?php

namespace App\Tests\Controller\Organization\Fleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class FleetFamilyControllerTest extends WebTestCase
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
    public function testOrgaFleetsFamily(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'shipInfo' => [
                    'id' => '4',
                    'productionStatus' => 'ready',
                    'minCrew' => 1,
                    'maxCrew' => 1,
                    'name' => 'Aurora MR',
                    'size' => 'small',
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/rsi-aurora/Aurora-MR',
                    'manufacturerName' => 'Roberts Space Industries',
                    'manufacturerCode' => 'RSI',
                    'chassisId' => '1',
                    'chassisName' => 'Aurora',
                    'mediaUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/source/Rsi_aurora_mr_storefront_visual.jpg',
                    'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/store_small/Rsi_aurora_mr_storefront_visual.jpg',
                ],
                'countTotalOwners' => '1',
                'countTotalShips' => '1',
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsFamilyNotPrivateRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/1', [], [], [
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
    public function testOrgaFleetsFamilyNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/1', [], [], [
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
    public function testOrgaFleetsFamilyAuthAdmin(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'shipInfo' => [
                    'id' => '4',
                    'productionStatus' => 'ready',
                    'minCrew' => 1,
                    'maxCrew' => 1,
                    'name' => 'Aurora MR',
                    'size' => 'small',
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/rsi-aurora/Aurora-MR',
                    'manufacturerName' => 'Roberts Space Industries',
                    'manufacturerCode' => 'RSI',
                    'chassisId' => '1',
                    'chassisName' => 'Aurora',
                    'mediaUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/source/Rsi_aurora_mr_storefront_visual.jpg',
                    'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/store_small/Rsi_aurora_mr_storefront_visual.jpg',
                ],
                'countTotalOwners' => '2',
                'countTotalShips' => '2',
            ],
        ], $json);
    }
}
