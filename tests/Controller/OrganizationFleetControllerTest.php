<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrganizationFleetControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']);
    }

//    /**
//     * @group functional
//     * @group orga_fleet
//     */
//    public function testOrgaStats(): void
//    {
//    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testCreateOrgaFleetFile(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/create-organization-fleet-file/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        /** @var BinaryFileResponse $response */
        $response = $this->client->getResponse();
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename=organization_fleet.json', $response->headers->get('Content-Disposition'));
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testCreateOrgaFleetFileNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/create-organization-fleet-file/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testExportOrgaFleet(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/export-orga-fleet/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        /** @var BinaryFileResponse $response */
        $response = $this->client->getResponse();
        $this->assertSame('application/csv', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename=export_flk.csv', $response->headers->get('Content-Disposition'));
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testExportOrgaFleetNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/export-orga-fleet/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
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
     * @group orga_fleet
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
     * @group orga_fleet
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
     * @group orga_fleet
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
     * @group orga_fleet
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
     * @group orga_fleet
     */
    public function testOrgaFleetsPrivateAuthAdminOnlyOrgaFail(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
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

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsAdmins(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => '3de45df8-3e84-4a69-8e9d-7bff1faa5281',
                'actualHandle' => ['handle' => 'ashuvidz'],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsAdminsNotAuthPublic(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/gardiens/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsAdminsNotPrivateRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsAdminsNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsAdminsAuthAdmin(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/admins', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => 'f0cdc161-f50c-4aad-b587-ae92d2d4a530',
                'actualHandle' => ['handle' => 'pulsar42_admin'],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsFamily(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
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
     * @group orga_fleet
     */
    public function testOrgaFleetsFamilyNotPrivateRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsFamilyNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsFamilyAuthAdmin(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/1', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
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

    /**
     * @group functional
     * @group orga_fleet
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
            'total' => 0,
            'users' => [],
        ], $json);
    }

    /**
     * @group functional
     * @group orga_fleet
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
     * @group orga_fleet
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
     * @group orga_fleet
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

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsUsersNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/users/Aurora%20MR', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsUsersAuthAdminPartialResult(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/users/Aurora%20MR', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'users' => [
                [
                    [
                        'id' => '4354da33-d2e7-442e-aa5d-22f9bbbdd132',
                        'citizen' => [
                            'id' => '165d226f-9ce4-4da3-a86e-e9e3fbb9c586',
                            'nickname' => 'Member2 de Pulsar42',
                            'actualHandle' => ['handle' => 'pulsar42_member2'],
                            'organizations' => [
                                [
                                    'id' => '1ff7c240-bad0-453b-a94c-6bef25363753',
                                    'organization' => [
                                        'organizationSid' => 'pulsar42',
                                        'name' => 'Pulsar42',
                                        'avatarUrl' => null,
                                    ],
                                    'rank' => 2,
                                    'rankName' => 'Member2',
                                ],
                            ],
                            'mainOrga' => [
                                'id' => '1ff7c240-bad0-453b-a94c-6bef25363753',
                                'organization' => [
                                    'organizationSid' => 'pulsar42',
                                    'name' => 'Pulsar42',
                                    'avatarUrl' => null,
                                ],
                                'rank' => 2,
                                'rankName' => 'Member2',
                            ],
                            'countRedactedOrganizations' => 0,
                            'redactedMainOrga' => false,
                        ],
                        'publicChoice' => 'orga',
                    ],
                    'countShips' => '1',
                ],
            ],
            'page' => 1,
            'lastPage' => 1,
            'total' => 1,
        ], $json);
    }

    /**
     * @group functional
     * @group orga_fleet
     */
    public function testOrgaFleetsHiddenUsers(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/hidden-users/Aurora%20MR', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, $json['hiddenUsers']); // Pulsar42Member1
    }
}
