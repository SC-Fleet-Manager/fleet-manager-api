<?php

namespace App\Tests\Controller\Funding;

use App\Entity\Funding;
use App\Tests\WebTestCase;

class PaymentControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     */
    public function test_index(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
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
    public function test_invalid_amount_minimum(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
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
    public function test_invalid_amount_maximum(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
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
    public function test_invalid_amount_blank(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
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
    public function test_index_not_auth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/funding/payment', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'amount' => 100,
        ]));

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('forbidden', $json['error']);
    }
}
