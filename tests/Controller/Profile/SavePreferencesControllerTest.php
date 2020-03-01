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
            'publicChoice' => 'private',
            'supporterVisible' => false,
        ]));
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
        $this->assertFalse($this->user->isSupporterVisible(), 'SupporterVisible must be false.');
        $this->assertSame('private', $this->user->getPublicChoice());
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
        $this->assertCount(2, $json['formErrors']['violations']);
        $this->assertSame('publicChoice', $json['formErrors']['violations'][0]['propertyPath']);
        $this->assertSame('You must choose a fleet policy.', $json['formErrors']['violations'][0]['title']);
        $this->assertSame('supporterVisible', $json['formErrors']['violations'][1]['propertyPath']);
        $this->assertSame('You must choose a supporter visibility.', $json['formErrors']['violations'][1]['title']);
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
