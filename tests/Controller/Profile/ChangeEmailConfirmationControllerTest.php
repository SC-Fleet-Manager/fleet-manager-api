<?php

namespace App\Tests\Controller\Profile;

use App\Tests\WebTestCase;

class ChangeEmailConfirmationControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group profile
     */
    public function testChangePasswordConfirmation(): void
    {
        $crawler = $this->client->request('GET', '/change-email-confirmation', [
            'token' => 'T9lNOh0FHR6Qdy94R7bTwrcqmYycuB8TM6tzUlwQZquxBIDGo29ERQ9jcD9LANPV',
            'id' => '013b7f07-142c-495e-8efc-2f9d21c50ee3', // changepasswordrequested@example.com
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        static::assertStringContainsString('Success! Your new email address has been set correctly. You will be redirected to the homepage in 5 seconds.', $crawler->filter('.alert-success')->text(null, false));
    }

    /**
     * @group functional
     * @group profile
     */
    public function testChangePasswordConfirmationBadToken(): void
    {
        $crawler = $this->client->request('GET', '/change-email-confirmation', [
            'token' => 'bad_token',
            'id' => '013b7f07-142c-495e-8efc-2f9d21c50ee3', // changepasswordrequested@example.com
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        static::assertStringContainsString('Sorry, this confirmation does not exist. Please check the web address in your email message.', $crawler->filter('.alert-danger')->text(null, false));
    }

    /**
     * @group functional
     * @group profile
     */
    public function testRegistrationConfirmationBadUser(): void
    {
        $crawler = $this->client->request('GET', '/change-email-confirmation', [
            'token' => 'random_token',
            'id' => '4e47d4f1-787c-447a-9e77-2a09fe41cc04', // not exist
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        static::assertStringContainsString('Sorry, this confirmation does not exist. Please check the web address in your email message.', $crawler->filter('.alert-danger')->text(null, false));
    }
}
