<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Organization;
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
     * @group organization
     */
    public function testSavePreferences(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ashuvidz']);
        $this->logIn($user);

        /** @var Organization $orga */
        $orga = $this->doctrine->getRepository(Organization::class)->findOneBy(['organizationSid' => 'flk']);
        $this->assertFalse($orga->isSupporterVisible(), 'SupporterVisible must be false.');

        $this->client->xmlHttpRequest('POST', '/api/organization/flk/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'publicChoice' => 'private',
            'supporterVisible' => true,
        ]));

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($orga->isSupporterVisible(), 'SupporterVisible must be true.');
        $this->assertSame('private', $orga->getPublicChoice());
    }

    /**
     * @group functional
     * @group organization
     */
    public function testSavePreferencesErrors(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ashuvidz']);
        $this->logIn($user);

        $this->client->xmlHttpRequest('POST', '/api/organization/flk/save-preferences', [], [], [
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

        $this->client->xmlHttpRequest('POST', '/api/organization/flk/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'publicChoice' => 'invalid_choice',
            'supporterVisible' => true,
        ]));

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
        $this->assertCount(1, $json['formErrors']['violations']);
        $this->assertSame('publicChoice', $json['formErrors']['violations'][0]['propertyPath']);
        $this->assertSame('You must select a valid option.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testSavePreferencesNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group organization
     */
    public function testSavePreferencesNotAdmin(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/organization/flk/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'publicChoice' => 'private',
        ]));

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights', $json['error']);
    }
}
