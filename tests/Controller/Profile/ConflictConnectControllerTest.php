<?php

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Tests\WebTestCase;

class ConflictConnectControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'linksocialnetworks-with-citizen-pending@example.com']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testIndex(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/profile/conflict-connect/discord', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertArraySubset([
            'me' => [
                'id' => '6b5f4962-4660-4705-bd48-2c587c42fe95',
                'email' => 'linksocialnetworks-with-citizen-pending@example.com',
                'nickname' => null,
                'discordId' => null,
                'citizen' => [
                    'id' => 'fb3ff308-f4ac-40b8-9be5-98d85035e2bf',
                    'number' => [
                        'number' => '54118218',
                    ],
                    'nickname' => 'Link social networks pending',
                    'actualHandle' => [
                        'handle' => 'link_social_networks_pending',
                    ],
                ],
            ],
            'alreadyLinkedUser' => [
                'id' => '4cbc6f40-3348-4e0f-a443-d4a4325eb728',
                'email' => null,
                'nickname' => 'Ashuvidz',
                'discordId' => '123456789002',
                'citizen' => [
                    'id' => '3de45df8-3e84-4a69-8e9d-7bff1faa5281',
                    'number' => [
                        'number' => '234567',
                    ],
                    'nickname' => 'VyrtualSynthese',
                    'actualHandle' => [
                        'handle' => 'ashuvidz',
                    ],
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testNoConflict(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/profile/conflict-connect/discord', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('no_pending_discord', $json['error']);
    }
}
