<?php

namespace App\Tests\Controller\Organization\Fleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class FleetFamilyControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsFamily(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/8502c9fd-6b1a-47e1-a7fc-6cb034b94da1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'shipInfo' => [
                    'id' => 'cbcb60c7-a780-4a59-b51d-0ad8021813bf',
                    'productionStatus' => 'ready',
                    'minCrew' => 1,
                    'maxCrew' => 1,
                    'name' => 'Aurora MR',
                    'size' => 'small',
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/rsi-aurora/Aurora-MR',
                    'manufacturerName' => 'Roberts Space Industries',
                    'manufacturerCode' => 'RSI',
                    'chassisId' => '8502c9fd-6b1a-47e1-a7fc-6cb034b94da1',
                    'chassisName' => 'Aurora',
                    'mediaUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/source/Rsi_aurora_mr_storefront_visual.jpg',
                    'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/store_small/Rsi_aurora_mr_storefront_visual.jpg',
                ],
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
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/8502c9fd-6b1a-47e1-a7fc-6cb034b94da1', [], [], [
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
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/8502c9fd-6b1a-47e1-a7fc-6cb034b94da1', [], [], [
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
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/8502c9fd-6b1a-47e1-a7fc-6cb034b94da1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'shipInfo' => [
                    'id' => 'cbcb60c7-a780-4a59-b51d-0ad8021813bf',
                    'productionStatus' => 'ready',
                    'minCrew' => 1,
                    'maxCrew' => 1,
                    'name' => 'Aurora MR',
                    'size' => 'small',
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/rsi-aurora/Aurora-MR',
                    'manufacturerName' => 'Roberts Space Industries',
                    'manufacturerCode' => 'RSI',
                    'chassisId' => '8502c9fd-6b1a-47e1-a7fc-6cb034b94da1',
                    'chassisName' => 'Aurora',
                    'mediaUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/source/Rsi_aurora_mr_storefront_visual.jpg',
                    'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/store_small/Rsi_aurora_mr_storefront_visual.jpg',
                ],
                'countTotalShips' => 3,
            ],
        ], $json);
    }
}
