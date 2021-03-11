<?php

namespace App\Tests\Controller\Profile;

use App\Entity\Funding;
use App\Entity\User;
use App\Tests\WebTestCase;

class PaymentControllerTest extends WebTestCase
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
     * @group funding
     */
    public function testIndex(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'amount' => 100,
        ]));

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertArraySubset([
            'gateway' => 'paypal',
            'paypalOrderId' => '123456ABCDEF',
            'paypalStatus' => 'CREATED',
            'amount' => 100,
            'currency' => 'USD',
            'createdAt' => '2019-12-11T20:22:33+00:00',
        ], $json);
        /** @var Funding $funding */
        $funding = $this->doctrine->getRepository(Funding::class)->find($json['id']);
        static::assertNotNull($funding, 'The entity funding must be persisted.');
        static::assertSame('Ioni', $funding->getUser()->getNickname());
    }

    /**
     * @group functional
     * @group funding
     */
    public function testInvalidAmountMinimum(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'amount' => 99,
        ]));

        static::assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('invalid_form', $json['error']);
        static::assertSame('amount', $json['formErrors']['violations'][0]['propertyPath']);
        static::assertSame('Sorry, but the minimum is $1.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @group functional
     * @group funding
     */
    public function testInvalidAmountMaximum(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'amount' => 1000000000,
        ]));

        static::assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('invalid_form', $json['error']);
        static::assertSame('amount', $json['formErrors']['violations'][0]['propertyPath']);
        static::assertSame('Could you donate a little less please? The maximum available is $9,999,999.99.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @group functional
     * @group funding
     */
    public function testInvalidAmountBlank(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], '{}');

        static::assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('invalid_form', $json['error']);
        static::assertSame('amount', $json['formErrors']['violations'][0]['propertyPath']);
        static::assertSame('Please provide an amount.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @group functional
     * @group funding
     */
    public function testIndexNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'amount' => 100,
        ]));

        static::assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('no_auth', $json['error']);
    }
}
