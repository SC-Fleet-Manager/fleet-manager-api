<?php

namespace App\Tests\Controller\Security;

use App\Entity\User;
use App\Message\Registration\SendLostPasswordRequestMail;
use App\Tests\WebTestCase;
use Symfony\Component\Messenger\Transport\TransportInterface;

class LostPasswordControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group security
     */
    public function testLostPasswordRequest(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'foo@example.com']);
        static::assertNull($user->getLostPasswordToken());
        static::assertNull($user->getLostPasswordRequestedAt());

        $this->client->xmlHttpRequest('POST', '/api/lost-password', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'foo@example.com',
        ]));

        static::assertSame(204, $this->client->getResponse()->getStatusCode());

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'foo@example.com']);
        static::assertNotNull($user->getLostPasswordToken());
        static::assertNotNull($user->getLostPasswordRequestedAt());

        /** @var TransportInterface $transport */
        $transport = static::$container->get('messenger.transport.sync');
        $envelopes = $transport->get();
        static::assertCount(1, $envelopes);
        static::assertInstanceOf(SendLostPasswordRequestMail::class, $envelopes[0]->getMessage());
        static::assertSame($user->getId()->toString(), $envelopes[0]->getMessage()->getUserId()->toString());
    }

    /**
     * @group functional
     * @group security
     */
    public function testLostPasswordRequestBadEmail(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/lost-password', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'foo@example',
        ]));

        static::assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('invalid_form', $json['error']);
        static::assertSame('This value is not a valid email address.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @group functional
     * @group security
     */
    public function testLostPasswordRequestMultiple(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/lost-password', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'foo@example.com',
        ]));

        static::assertSame(204, $this->client->getResponse()->getStatusCode());

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'foo@example.com']);
        $token = $user->getLostPasswordToken();
        $requestedAt = $user->getLostPasswordRequestedAt();

        /** @var TransportInterface $transport */
        $transport = static::$container->get('messenger.transport.sync');
        $envelopes = $transport->get();
        static::assertCount(1, $envelopes);

        // messenger transport reset
        $this->client->xmlHttpRequest('POST', '/api/lost-password', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'foo@example.com',
        ]));

        static::assertSame(204, $this->client->getResponse()->getStatusCode());

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['email' => 'foo@example.com']);
        static::assertSame($token, $user->getLostPasswordToken());
        static::assertSame($requestedAt->format('Y-m-d H:i:s'), $user->getLostPasswordRequestedAt()->format('Y-m-d H:i:s'));

        $transport = static::$container->get('messenger.transport.sync');
        $envelopes = $transport->get();
        static::assertCount(0, $envelopes);
    }
}
