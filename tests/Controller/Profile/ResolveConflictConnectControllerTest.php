<?php

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Tests\WebTestCase;

class ResolveConflictConnectControllerTest extends WebTestCase
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
    public function testKeepOtherCitizen(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/resolve-conflict-connect/discord', [
            'conflictChoice' => '3de45df8-3e84-4a69-8e9d-7bff1faa5281', // ashuvidz Citizen
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->find($this->user->getId());
        $this->assertSame('3de45df8-3e84-4a69-8e9d-7bff1faa5281', $user->getCitizen()->getId()->toString());
        $this->assertSame('ashuvidz', $user->getCitizen()->getActualHandle()->getHandle());
        $this->assertSame('123456789002', $user->getDiscordId());
        $this->assertNull($user->getPendingDiscordId());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testKeepMyCitizen(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/resolve-conflict-connect/discord', [
            'conflictChoice' => 'fb3ff308-f4ac-40b8-9be5-98d85035e2bf', // link_social_networks_pending Citizen
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->find($this->user->getId());
        $this->assertSame('fb3ff308-f4ac-40b8-9be5-98d85035e2bf', $user->getCitizen()->getId()->toString());
        $this->assertSame('link_social_networks_pending', $user->getCitizen()->getActualHandle()->getHandle());
        $this->assertSame('123456789002', $user->getDiscordId());
        $this->assertNull($user->getPendingDiscordId());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testBadChoice(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/resolve-conflict-connect/discord', [
            'conflictChoice' => '8c1c7828-228a-482d-bf75-d5b6c65694b2', // does not exist
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('You must choose a Citizen among the proposals.', $json['formErrors']['violations'][0]['title']);
    }
}
