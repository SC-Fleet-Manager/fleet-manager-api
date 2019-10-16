<?php

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Tests\WebTestCase;

class SavePreferencesControllerTest extends WebTestCase
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
    public function testSavePreferences(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'publicChoice' => 'private',
        ]));
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testSavePreferencesNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/profile/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }
}
