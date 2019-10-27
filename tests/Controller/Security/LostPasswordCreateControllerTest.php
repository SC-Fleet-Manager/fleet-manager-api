<?php

namespace App\Tests\Controller\Security;

use App\Tests\WebTestCase;

class LostPasswordCreateControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group security
     */
    public function testLostPasswordCreate(): void
    {
        $crawler = $this->client->request('GET', '/lost-password-create', [
            'token' => '4kTh4QvvKm6LtHcnJ0Oj6dzfXvgirQKt8LHrIu5JqlOWl1AMfl8S3KS6waUiQFFA',
            'id' => 'd477c5bd-4b1d-4a51-adf0-091ce599f408', // user_lostpasswordrequested@example.com
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->assertContains('Your new password', $crawler->filter('label')->text());
        $this->assertContains('Change my password', $crawler->filter('button')->text());

        $crawler = $this->client->submitForm('Change my password', [
            'lost_password_create_form[password]' => '123456789',
        ]);

        $this->assertContains('Success! Your new password is now set correctly. You will be redirected to the homepage in 5 seconds.', $crawler->filter('.alert-success')->text());
    }

    /**
     * @group functional
     * @group security
     */
    public function testLostPasswordCreateNotExist(): void
    {
        $crawler = $this->client->request('GET', '/lost-password-create', [
            'token' => 'bad_token',
            'id' => 'd477c5bd-4b1d-4a51-adf0-091ce599f408', // user_lostpasswordrequested@example.com
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->assertContains('Sorry, we have not found you, please make a lost password request again. Go to homepage.', $crawler->filter('.alert-danger')->text());
    }

    /**
     * @group functional
     * @group security
     */
    public function testLostPasswordCreateTokenExpired(): void
    {
        $crawler = $this->client->request('GET', '/lost-password-create', [
            'token' => '2X9FV6J4l8itnDZHSLYDUFDorK9uDeWQski6YmqwzGPTcaONoMdV7Rc0xwLiQKHB',
            'id' => 'f27a6609-57cc-4e98-b762-2d929d046afe', // user_lostpasswordexpired@example.com
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $this->assertContains('Sorry, your lost password token has expired, please make a new lost password request. Go to homepage.', $crawler->filter('.alert-danger')->text());
    }
}
