<?php

namespace App\Tests\Controller\Organization;

use App\Entity\User;
use App\Tests\WebTestCase;

class RefreshOrganizationControllerTest extends WebTestCase
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
     * @group organization
     */
    public function testRefreshOrgaSuccess(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ashuvidz']); // admin
        $this->logIn($user);
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/refresh-orga', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'totalItems' => 2,
            'countHiddenMembers' => 3,
            'members' => [
                [
                    'infos' => [
                        'handle' => 'ashuvidz',
                        'nickname' => 'Ashuvidz',
                        'avatarUrl' => null,
                        'rank' => 5,
                        'rankName' => 'Boss',
                    ],
                    'status' => 'registered',
                ],
                [
                    'lastFleetUploadDate' => '2019-04-05',
                    'infos' => [
                        'handle' => 'ionni',
                        'nickname' => 'Ioni',
                        'avatarUrl' => null,
                        'rank' => 1,
                        'rankName' => 'Soldat',
                    ],
                    'status' => 'fleet_uploaded',
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testRefreshOrgaForbidden(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/refresh-orga', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights', $json['error']);
    }
}
