<?php

namespace App\Tests\Controller\Organization\Fleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class FleetUsersControllerTest extends WebTestCase
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
     * @group organization_fleet
     *
     * @group toto
     */
    public function testOrgaFleetsUsersPrivateAuth(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/flk/users/Cutlass%20Black', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->debugHtml();


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
     * @group organization_fleet
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
     * @group organization_fleet
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
     * @group organization_fleet
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
     * @group organization_fleet
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
}
