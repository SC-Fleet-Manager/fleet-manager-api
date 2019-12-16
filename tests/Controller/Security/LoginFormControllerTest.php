<?php

namespace App\Tests\Controller\Security;

use App\Tests\WebTestCase;

class LoginFormControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group security
     */
    public function testLoginForm(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/login/check-form-login', [
            '_username' => 'alreadyconfirmed@example.com',
            '_password' => '123456',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('/profile', $json['redirectTo']);
    }

    /**
     * @group functional
     * @group security
     */
    public function testLoginFormNotConfirmed(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/login/check-form-login', [
            '_username' => 'foo@example.com',
            '_password' => '123456',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_confirmed_registration', $json['error']);
        $this->assertSame('You have not confirmed your registration yet. Please check your emails.', $json['errorMessage']);
    }

    /**
     * @group functional
     * @group security
     */
    public function testLoginFormBadCredentials(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/login/check-form-login', [
            '_username' => 'notexist@example.com',
            '_password' => '123456',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Invalid credentials.', $json['error']);
        $this->assertSame('Bad credentials.', $json['errorMessage']);
    }

    /**
     * @group functional
     * @group security
     */
    public function testLoginFormNotRegisteredButSSO(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/login/check-form-login', [
            '_username' => 'Ioni',
            '_password' => '',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('Invalid credentials.', $json['error']);
        $this->assertSame('Bad credentials.', $json['errorMessage']);
    }
}
