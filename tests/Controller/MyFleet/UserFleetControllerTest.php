<?php

namespace App\Tests\Controller\MyFleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class UserFleetControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group api
     */
    public function testUserFleetPublic(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/user-fleet/ionni', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'fleet' => [
                'ships' => [
                    [
                        'id' => 'a75256db-07fa-4f49-95f9-9f44bd7fbd72',
                        'name' => 'Cutlass Black',
                        'normalizedName' => 'Cutlass Black',
                        'manufacturer' => 'Drake',
                        'pledgeDate' => '2019-04-10T00:00:00+00:00',
                        'insured' => true,
                        'galaxyId' => 'e37c618b-3ec6-4d4d-92b6-5aed679962a2',
                    ],
                ],
            ],
            'shipInfos' => [
                'e37c618b-3ec6-4d4d-92b6-5aed679962a2' => [
                    'id' => 'e37c618b-3ec6-4d4d-92b6-5aed679962a2',
                    'productionStatus' => 'ready',
                    'minCrew' => 2,
                    'maxCrew' => 2,
                    'name' => 'Cutlass Black',
                    'size' => 'medium',
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/drake-cutlass/Cutlass-Black',
                    'manufacturerName' => 'Drake Interplanetary',
                    'manufacturerCode' => 'DRAK',
                    'chassisId' => 'f92c7c98-a8d2-4c79-b34c-c728d4fffbfc',
                    'chassisName' => 'Cutlass',
                    'mediaUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/source/Drake_cutlass_storefront_visual.jpg',
                    'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/store_small/Drake_cutlass_storefront_visual.jpg',
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group api
     */
    public function testUserFleetPrivate(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/user-fleet/ashuvidz', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_rights', $json['error']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testUserFleetOrgaMember(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/user-fleet/pulsar42_member3', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'fleet' => [
                'ships' => [
                    [
                        'id' => '56c439ff-51ea-4dc1-bbdf-5e53c2c7e434',
                        'name' => 'Aurora MR',
                        'normalizedName' => 'Aurora MR',
                        'galaxyId' => 'cbcb60c7-a780-4a59-b51d-0ad8021813bf',
                    ],
                ],
            ],
            'shipInfos' => [
                'cbcb60c7-a780-4a59-b51d-0ad8021813bf' => [
                    'id' => 'cbcb60c7-a780-4a59-b51d-0ad8021813bf',
                    'name' => 'Aurora MR',
                ],
            ],
        ], $json);
    }
}
