<?php

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Message\Profile\SendChangeEmailRequestMail;
use App\Tests\WebTestCase;
use Symfony\Component\Messenger\Transport\TransportInterface;

class ChangeEmailRequestControllerTest extends WebTestCase
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
    public function testChangeEmailRequest(): void
    {
        static::assertSame('ioni@example.com', $this->user->getEmail());
        static::assertNull($this->user->getPendingEmail());
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/change-email-request', [
            'newEmail' => 'new-email@example.com',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        static::assertSame(204, $this->client->getResponse()->getStatusCode());
        static::assertSame('ioni@example.com', $this->user->getEmail());
        static::assertSame('new-email@example.com', $this->user->getPendingEmail());

        /** @var TransportInterface $transport */
        $transport = static::$container->get('messenger.transport.sync');
        $envelopes = $transport->get();
        static::assertCount(1, $envelopes);
        static::assertInstanceOf(SendChangeEmailRequestMail::class, $envelopes[0]->getMessage());
        static::assertSame($this->user->getId()->toString(), $envelopes[0]->getMessage()->getUserId()->toString());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testChangeEmailRequestBadValue(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/change-email-request', [
            'newEmail' => 'bad-email',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('invalid_form', $json['error']);
        static::assertSame('This value is not a valid email address.', $json['formErrors']['violations'][0]['title']);
    }
}
