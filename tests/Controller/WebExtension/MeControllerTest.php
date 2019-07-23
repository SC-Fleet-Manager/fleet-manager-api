<?php

namespace App\Tests\Controller\WebExtension;

use App\Entity\User;
use App\Tests\WebTestCase;

class MeControllerTest extends WebTestCase
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
     * @group api
     */
    public function testMe(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/me', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('d92e229e-e743-4583-905a-e02c57eacfe0', $json['id']);
        $this->assertSame('Ioni', $json['nickname']);
        $this->assertSame('2019-04-02T11:22:33+00:00', $json['createdAt']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testMeNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/me', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }
}
