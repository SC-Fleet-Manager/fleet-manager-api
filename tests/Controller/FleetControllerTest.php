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
        $this->client->xmlHttpRequest('GET', '/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    public function testMyFleet(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/my-fleet', [], [], [
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
        $this->client->xmlHttpRequest('GET', '/user-fleet/ionni', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserFleetPrivate(): void
    {
        $this->client->xmlHttpRequest('GET', '/user-fleet/ashuvidz', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());

        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_rights', $json['error']);
    }

    public function testOrgaFleets(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/fleets/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'tableHeaders' => [
                'shipName' => [
                    'label' => 'Ships',
                    'sortable' => true,
                ],
                'shipManufacturer' => [
                    'label' => 'Manufacturers',
                    'sortable' => true,
                ],
                'totalAvailable' => [
                    'label' => 'Total available',
                    'sortable' => true,
                ],
                'ionni' => [
                    'label' => 'ionni',
                    'sortable' => true,
                ],
                'ashuvidz' => [
                    'label' => 'ashuvidz',
                    'sortable' => true,
                ],
            ],
            'fleets' => [
                [
                    '_cellVariants' => [
                        'shipName' => 'success',
                        'ionni' => 'success',
                        'ashuvidz' => '',
                    ],
                    'shipName' => 'Cutlass Black',
                    'shipManufacturer' => 'DRAK',
                    'totalAvailable' => 1,
                    'ionni' => 1,
                    'ashuvidz' => null,
                ],
            ],
            'ships' => [
                'Cutlass Black' => 'Cutlass Black',
            ],
            'citizens' => [
                '7275c744-6a69-43c2-9ebf-1491a104d5e7' => 'ionni',
                '3de45df8-3e84-4a69-8e9d-7bff1faa5281' => 'ashuvidz',
            ],
            'shipInfos' => [
                [
                    'id' => '56',
                    'productionStatus' => 'ready',
                    'minCrew' => 2,
                    'maxCrew' => 2,
                    'name' => 'Cutlass Black',
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/drake-cutlass/Cutlass-Black',
                    'manufacturerName' => 'Drake Interplanetary',
                    'manufacturerCode' => 'DRAK',
                    'mediaUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/source/Drake_cutlass_storefront_visual.jpg',
                    'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/store_small/Drake_cutlass_storefront_visual.jpg',
                ]
            ]
        ], $json);
    }

    public function testOrgaFleetsNotExists(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/fleets/foobar', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('bad_organisation', $json['error']);
    }

    public function testOrgaFleetsNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/fleets/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }
}
