<?php

namespace App\Tests\Controller\Organization;

use App\Entity\User;
use App\Tests\WebTestCase;

class RefreshMembersControllerTest extends WebTestCase
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
    public function testRefreshMembersSuccess(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ashuvidz']); // admin
        $this->logIn($user);
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/refresh-member/ionni', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group organization
     */
    public function testRefreshMembersForbidden(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/refresh-member/ionni', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights', $json['error']);
    }
}
