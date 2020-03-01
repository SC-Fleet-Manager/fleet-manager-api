<?php

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Tests\WebTestCase;

class RefreshRsiProfileControllerTest extends WebTestCase
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
     * @group profile
     */
    public function testRefreshRsiProfile(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'needRefresh']);
        $this->logIn($user);

        $this->assertNull($user->getCitizen()->getMainOrga(), 'Main orga of user need_refresh must be null before refresh.');
        $this->assertCount(0, $user->getCitizen()->getOrganizations(), 'User need_refresh must have no orgas before refresh.');

        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        $this->assertSame('80db0703-dd43-49a0-93d3-89947b9ab321', $user->getCitizen()->getMainOrga()->getOrganization()->getId()->toString());
        $this->assertSame('flk', $user->getCitizen()->getMainOrga()->getOrganization()->getOrganizationSid());

        $this->assertCount(2, $user->getCitizen()->getOrganizations());
        $orga1 = $user->getCitizen()->getOrganizations()[0];
        $this->assertSame('80db0703-dd43-49a0-93d3-89947b9ab321', $orga1->getOrganization()->getId()->toString());
        $this->assertSame('flk', $orga1->getOrganization()->getOrganizationSid());
        $this->assertSame('FallKrom', $orga1->getOrganization()->getName());
        $this->assertSame(1, $orga1->getRank());
        $this->assertSame('Newbie', $orga1->getRankName());

        $orga2 = $user->getCitizen()->getOrganizations()[1];
        $this->assertSame('901ccbf8-fa63-4b07-81aa-f10f60954715', $orga2->getOrganization()->getId()->toString());
        $this->assertSame('gardiens', $orga2->getOrganization()->getOrganizationSid());
        $this->assertSame('Les Gardiens', $orga2->getOrganization()->getName());
        $this->assertSame(1, $orga2->getRank());
        $this->assertSame('Noob', $orga2->getRankName());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testRefreshRsiProfileTooManyRefresh(): void
    {
        $this->logIn($this->user);
        $this->client->insulate();
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->client->insulate();
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('too_many_refresh', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testRefreshRsiProfileNoCitizen(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'NoCitizen']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_citizen', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testRefreshRsiProfileNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }
}
