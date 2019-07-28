<?php

namespace App\Tests\Controller\MyFleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class ProfileControllerTest extends WebTestCase
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
     * @group profile
     */
    public function testIndex(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'id' => 'd92e229e-e743-4583-905a-e02c57eacfe0',
            'username' => 'Ioni',
            'token' => '4682bc58961264de31d38bf6af18cfe717ab2ba59f34b906668b4d7c0ca65b33',
            'citizen' => [
                'id' => '7275c744-6a69-43c2-9ebf-1491a104d5e7',
                'number' => [
                    'number' => '123456',
                ],
                'actualHandle' => [
                    'handle' => 'ionni',
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
                'bio' => 'This is my bio',
                'countRedactedOrganizations' => 0,
                'redactedMainOrga' => false,
            ],
            'publicChoice' => 'public',
            'createdAt' => '2019-04-02T11:22:33+00:00',
            'nickname' => 'Ioni',
        ], $json);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testIndexNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }
}
