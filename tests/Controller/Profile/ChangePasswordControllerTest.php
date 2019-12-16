<?php

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Tests\WebTestCase;

class ChangePasswordControllerTest extends WebTestCase
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
    public function testChangePassword(): void
    {
        $this->assertSame('123456', $this->user->getPassword());
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/change-password', [
            'oldPassword' => '123456',
            'newPassword' => '456789',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
        $this->assertSame('456789', $this->user->getPassword());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testChangePasswordBadOldPassword(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/change-password', [
            'oldPassword' => 'bad_password',
            'newPassword' => '456789',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
        $this->assertSame('Your current password is wrong.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testChangePasswordTooShortNewPassword(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/change-password', [
            'oldPassword' => '123456',
            'newPassword' => '123',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
        $this->assertSame("Some extra characters and you'll have the 6 required. ;-)", $json['formErrors']['violations'][0]['title']);
    }
}
