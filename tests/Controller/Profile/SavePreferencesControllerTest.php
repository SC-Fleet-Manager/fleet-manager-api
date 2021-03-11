<?php

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Tests\WebTestCase;

class SavePreferencesControllerTest extends WebTestCase
{
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testSavePreferences(): void
    {
        $this->logIn($this->user);

        $this->assertTrue($this->user->isSupporterVisible(), 'SupporterVisible must be true.');

        $this->client->xmlHttpRequest('POST', '/api/profile/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'supporterVisible' => false,
        ]));
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->user->isSupporterVisible(), 'SupporterVisible must be false.');
    }

    /**
     * @group functional
     * @group profile
     */
    public function testSavePreferencesErrors(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{}');

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
        $this->assertCount(1, $json['formErrors']['violations']);
        $this->assertSame('supporterVisible', $json['formErrors']['violations'][0]['propertyPath']);
        $this->assertSame('You must choose a supporter visibility.', $json['formErrors']['violations'][0]['title']);
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
