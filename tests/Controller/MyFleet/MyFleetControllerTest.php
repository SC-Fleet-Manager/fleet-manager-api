<?php

namespace App\Tests\Controller\MyFleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class MyFleetControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group api
     */
    public function testMyFleetNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testMyFleet(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'fleet' => [
                'id' => '8fb44dad-8d40-447e-a670-62b3192d5521',
                'version' => 1,
                'uploadDate' => '2019-04-05T00:00:00+00:00',
                'ships' => [
                    [
                        'id' => 'a75256db-07fa-4f49-95f9-9f44bd7fbd72',
                        'name' => 'Cutlass Black',
                        'normalizedName' => 'Cutlass Black',
                        'galaxyId' => 'e37c618b-3ec6-4d4d-92b6-5aed679962a2',
                        'manufacturer' => 'Drake',
                        'pledgeDate' => '2019-04-10T00:00:00+00:00',
                        'cost' => 110,
                        'insured' => true,
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
                    'cargoCapacity' => null,
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/drake-cutlass/Cutlass-Black',
                    'manufacturerId' => null,
                    'manufacturerName' => 'Drake Interplanetary',
                    'manufacturerCode' => 'DRAK',
                    'chassisId' => 'f92c7c98-a8d2-4c79-b34c-c728d4fffbfc',
                    'chassisName' => 'Cutlass',
                    'mediaUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/source/Drake_cutlass_storefront_visual.jpg',
                    'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/store_small/Drake_cutlass_storefront_visual.jpg',
                ],
            ],
        ], $json);
        $this->assertArrayHasKey('shipInfos', $json);
    }

    /**
     * @group functional
     * @group api
     */
    public function testMyFleet2(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'iHaveShips']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        usort($json['fleet']['ships'], static function (array $ship1, array $ship2) {
            return $ship1['name'] <=> $ship2['name'];
        });
        $this->assertArraySubset([
            'fleet' => [
                'id' => '7418a47d-6bdd-4023-bd4e-5d2e0fcff6c1',
                'version' => 1,
                'uploadDate' => '2019-04-07T00:00:00+00:00',
                'ships' => [
                    [
                        'id' => 'e3fd1f7d-5386-422f-bbef-753420c7642d',
                        'name' => 'Aurora MR',
                        'normalizedName' => 'Aurora MR',
                        'galaxyId' => 'cbcb60c7-a780-4a59-b51d-0ad8021813bf',
                        'manufacturer' => 'RSI',
                        'pledgeDate' => '2019-04-25T00:00:00+00:00',
                        'cost' => 35,
                        'insured' => true,
                        'insuranceType' => null,
                        'insuranceDuration' => null,
                    ],
                    [
                        'id' => 'afb85b3b-6e2b-45bb-b099-307890f129c9',
                        'name' => 'Constellation Andromeda',
                        'normalizedName' => 'Constellation Andromeda',
                        'galaxyId' => 'f43fa89e-d34f-43d2-807d-5e8bf8c8929a',
                        'manufacturer' => 'RSI',
                        'pledgeDate' => '2019-03-30T00:00:00+00:00',
                        'cost' => 225,
                        'insured' => false,
                        'insuranceType' => null,
                        'insuranceDuration' => null,
                    ],
                    [
                        'id' => '8212688b-d2ed-47c2-a3da-422854a4a026',
                        'name' => 'Cutlass Black',
                        'normalizedName' => 'Cutlass Black',
                        'galaxyId' => 'e37c618b-3ec6-4d4d-92b6-5aed679962a2',
                        'manufacturer' => 'Drake',
                        'pledgeDate' => '2019-03-21T00:00:00+00:00',
                        'cost' => 95,
                        'insured' => true,
                        'insuranceType' => null,
                        'insuranceDuration' => null,
                    ],
                    [
                        'id' => '35c1af61-7dca-4f42-8d52-fc0439e23af2',
                        'name' => 'Dragonfly Black',
                        'normalizedName' => 'Dragonfly Black',
                        'galaxyId' => '05e980c5-6425-4fe4-a3c2-d69a0d568e40',
                        'manufacturer' => 'Drake',
                        'pledgeDate' => '2018-11-27T00:00:00+00:00',
                        'cost' => 40,
                        'insured' => false,
                        'insuranceType' => null,
                        'insuranceDuration' => null,
                    ],
                    [
                        'id' => '7e7951c4-6dc6-40c5-87ef-6412b7ac658f',
                        'name' => 'Orion',
                        'normalizedName' => 'Orion',
                        'galaxyId' => '9950adb5-9151-4760-9073-080416120fca',
                        'manufacturer' => 'RSI',
                        'pledgeDate' => '2019-02-17T00:00:00+00:00',
                        'cost' => 325,
                        'insured' => true,
                        'insuranceType' => null,
                        'insuranceDuration' => null,
                    ],
                    [
                        'id' => 'f25131ad-a74d-4efb-9fbf-81665d09feb8',
                        'name' => 'Ranger CV',
                        'normalizedName' => 'Ranger CV',
                        'galaxyId' => 'f250a2b7-76ea-481f-84b5-3e2e96d40e84',
                        'manufacturer' => 'Tumbril',
                        'pledgeDate' => '2019-04-20T00:00:00+00:00',
                        'cost' => 30,
                        'insured' => false,
                        'insuranceType' => null,
                        'insuranceDuration' => null,
                    ],
                ],
            ],
            'shipInfos' => [
                'e37c618b-3ec6-4d4d-92b6-5aed679962a2' => [
                    'id' => 'e37c618b-3ec6-4d4d-92b6-5aed679962a2',
                    'name' => 'Cutlass Black',
                ],
                'cbcb60c7-a780-4a59-b51d-0ad8021813bf' => [
                    'id' => 'cbcb60c7-a780-4a59-b51d-0ad8021813bf',
                    'name' => 'Aurora MR',
                ],
                'f250a2b7-76ea-481f-84b5-3e2e96d40e84' => [
                    'id' => 'f250a2b7-76ea-481f-84b5-3e2e96d40e84',
                    'name' => 'Ranger CV',
                ],
                '05e980c5-6425-4fe4-a3c2-d69a0d568e40' => [
                    'id' => '05e980c5-6425-4fe4-a3c2-d69a0d568e40',
                    'name' => 'Dragonfly Black',
                ],
                'f43fa89e-d34f-43d2-807d-5e8bf8c8929a' => [
                    'id' => 'f43fa89e-d34f-43d2-807d-5e8bf8c8929a',
                    'name' => 'Constellation Andromeda',
                ],
                '9950adb5-9151-4760-9073-080416120fca' => [
                    'id' => '9950adb5-9151-4760-9073-080416120fca',
                    'name' => 'Orion',
                ],
            ],
        ], $json);
        $this->assertArrayHasKey('shipInfos', $json);
    }
}
