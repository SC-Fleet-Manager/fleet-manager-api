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

    /**
     * @group functional
     * @group fleet
     */
    public function testMyFleetNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testMyFleet(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/my-fleet', [], [], [
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
                    ],
                ],
            ],
        ], $json);
        $this->assertArrayHasKey('shipInfos', $json);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testUserFleetPublic(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/user-fleet/ionni', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'fleet' => [
                'ships' => [
                    [
                        'id' => 'a75256db-07fa-4f49-95f9-9f44bd7fbd72',
                        'name' => 'Cutlass Black',
                        'manufacturer' => 'Drake',
                        'pledgeDate' => '2019-04-10T00:00:00+00:00',
                        'insured' => true,
                    ],
                ],
            ],
            'shipInfos' => [
                [
                    'id' => '56',
                    'productionStatus' => 'ready',
                    'minCrew' => 2,
                    'maxCrew' => 2,
                    'name' => 'Cutlass Black',
                    'size' => 'medium',
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/drake-cutlass/Cutlass-Black',
                    'manufacturerName' => 'Drake Interplanetary',
                    'manufacturerCode' => 'DRAK',
                    'chassisId' => '6',
                    'chassisName' => 'Cutlass',
                    'mediaUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/source/Drake_cutlass_storefront_visual.jpg',
                    'mediaThumbUrl' => 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/store_small/Drake_cutlass_storefront_visual.jpg',
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testUserFleetPrivate(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/user-fleet/ashuvidz', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_rights', $json['error']);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testOrgaFleetsPublicNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/gardiens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
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
     * @group fleet
     */
    public function testOrgaFleetsPrivateAuth(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
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
     * @group fleet
     */
    public function testOrgaFleetsPrivateAuthBadOrga(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/not_exist', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('bad_organization', $json['error']);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testOrgaFleetsPrivateNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testOrgaFleetsPrivateAuthPrivateOrga(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testOrgaFleetsUsersPublicNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/gardiens/users/Aurora%20MR', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'page' => 1,
            'lastPage' => 1,
            'total' => 2,
            'users' => [
                [
                    [
                        'id' => '46380677-9915-4b7c-87ba-418840cb1772',
                        'citizen' => [
                            'id' => '1256a87e-65bf-4fb1-9810-35d1f8053be8',
                            'nickname' => 'Gardien1',
                            'actualHandle' => ['handle' => 'gardien1'],
                            'organizations' => [
                                [
                                    'id' => 'befc7409-a026-4226-aefd-288b1d03b8ef',
                                    'organization' => [
                                        'organizationSid' => 'gardiens',
                                        'name' => 'Les Gardiens',
                                        'avatarUrl' => null,
                                    ],
                                    'rank' => 5,
                                    'rankName' => 'Big guard',
                                ],
                            ],
                            'mainOrga' => [
                                'id' => 'befc7409-a026-4226-aefd-288b1d03b8ef',
                                'organization' => [
                                    'organizationSid' => 'gardiens',
                                    'name' => 'Les Gardiens',
                                    'avatarUrl' => null,
                                ],
                                'rank' => 5,
                                'rankName' => 'Big guard',
                            ],
                            'countRedactedOrganizations' => 0,
                            'redactedMainOrga' => false,
                        ],
                        'publicChoice' => 'public',
                    ],
                    'countShips' => '1',
                ],
                [
                    [
                        'id' => '503e3bc1-cff9-42b8-9f27-a6064b0a78f2',
                        'citizen' => [
                            'id' => '08cc11ec-26ac-4638-8e03-c40b857d32bd',
                            'nickname' => 'I Have Ships',
                            'actualHandle' => [
                                'handle' => 'ihaveships',
                            ],
                            'organizations' => [
                                [
                                    'id' => 'a193b472-501d-4b97-8dbc-c4076618f347',
                                    'organization' => [
                                        'organizationSid' => 'flk',
                                        'name' => 'FallKrom',
                                        'avatarUrl' => null,
                                    ],
                                    'rank' => 2,
                                    'rankName' => 'Peasant',
                                ],
                                [
                                    'id' => 'fa91e3a0-4930-43de-b202-9d5972681031',
                                    'organization' => [
                                        'organizationSid' => 'gardiens',
                                        'name' => 'Les Gardiens',
                                        'avatarUrl' => null,
                                    ],
                                    'rank' => 4,
                                    'rankName' => 'Lord',
                                ],
                            ],
                            'mainOrga' => [
                                'id' => 'fa91e3a0-4930-43de-b202-9d5972681031',
                                'organization' => [
                                    'organizationSid' => 'gardiens',
                                    'name' => 'Les Gardiens',
                                    'avatarUrl' => null,
                                ],
                                'rank' => 4,
                                'rankName' => 'Lord',
                            ],
                            'countRedactedOrganizations' => 0,
                            'redactedMainOrga' => false,
                        ],
                        'publicChoice' => 'public',
                    ],
                    'countShips' => '1',
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testOrgaFleetsUsersPrivateAuth(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/users/Cutlass%20Black', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'page' => 1,
            'lastPage' => 1,
            'total' => 2,
            'users' => [
                [
                    [
                        'id' => '503e3bc1-cff9-42b8-9f27-a6064b0a78f2',
                        'citizen' => [
                            'id' => '08cc11ec-26ac-4638-8e03-c40b857d32bd',
                            'nickname' => 'I Have Ships',
                            'actualHandle' => [
                                'handle' => 'ihaveships',
                            ],
                            'organizations' => [
                                [
                                    'id' => 'a193b472-501d-4b97-8dbc-c4076618f347',
                                    'organization' => [
                                        'organizationSid' => 'flk',
                                        'name' => 'FallKrom',
                                        'avatarUrl' => null,
                                    ],
                                    'rank' => 2,
                                    'rankName' => 'Peasant',
                                ],
                                [
                                    'id' => 'fa91e3a0-4930-43de-b202-9d5972681031',
                                    'organization' => [
                                        'organizationSid' => 'gardiens',
                                        'name' => 'Les Gardiens',
                                        'avatarUrl' => null,
                                    ],
                                    'rank' => 4,
                                    'rankName' => 'Lord',
                                ],
                            ],
                            'mainOrga' => [
                                'id' => 'fa91e3a0-4930-43de-b202-9d5972681031',
                                'organization' => [
                                    'organizationSid' => 'gardiens',
                                    'name' => 'Les Gardiens',
                                    'avatarUrl' => null,
                                ],
                                'rank' => 4,
                                'rankName' => 'Lord',
                            ],
                            'countRedactedOrganizations' => 0,
                            'redactedMainOrga' => false,
                        ],
                        'publicChoice' => 'public',
                    ],
                    'countShips' => '1',
                ],
                [
                    [
                        'id' => 'd92e229e-e743-4583-905a-e02c57eacfe0',
                        'citizen' => [
                            'id' => '7275c744-6a69-43c2-9ebf-1491a104d5e7',
                            'nickname' => 'Ioni14',
                            'actualHandle' => ['handle' => 'ionni'],
                            'organizations' => [
                                [
                                    'id' => '41ade55e-6d32-419c-9e48-169fd6c61f34',
                                    'organization' => [
                                        'organizationSid' => 'flk',
                                        'name' => 'FallKrom',
                                        'avatarUrl' => null,
                                    ],
                                    'rank' => 1,
                                    'rankName' => 'Citoyen',
                                ],
                            ],
                            'mainOrga' => [
                                'id' => '41ade55e-6d32-419c-9e48-169fd6c61f34',
                                'organization' => [
                                    'organizationSid' => 'flk',
                                    'name' => 'FallKrom',
                                    'avatarUrl' => null,
                                ],
                                'rank' => 1,
                                'rankName' => 'Citoyen',
                            ],
                            'countRedactedOrganizations' => 0,
                            'redactedMainOrga' => false,
                        ],
                        'publicChoice' => 'public',
                    ],
                    'countShips' => '1',
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testOrgaFleetsUsersPrivateAuthPrivateOrga(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/users/Cutlass%20Black', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group fleet
     */
    public function testOrgaFleetsUsersPrivateNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/users/Cutlass%20Black', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }
}
