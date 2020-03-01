<?php

namespace App\Tests\Controller\Organization;

use App\Entity\User;
use App\Tests\WebTestCase;

class CitizensControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testCitizens(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => '08cc11ec-26ac-4638-8e03-c40b857d32bd',
                'label' => 'ihaveships',
            ],
            [
                'id' => '7275c744-6a69-43c2-9ebf-1491a104d5e7',
                'label' => 'ionni',
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testCitizensNotPublicRights(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testCitizensNotPrivateRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_private', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testCitizensNotAdminRights(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Member1']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/pulsar42/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_admin', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testCitizensAdmin(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/pulsar42/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
