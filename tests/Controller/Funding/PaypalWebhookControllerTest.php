<?php

namespace App\Tests\Controller\Funding;

use App\Entity\Funding;
use App\Message\Funding\SendOrderRefundMail;
use App\Tests\Service\PayPal\MockPayPalHttpClient;
use App\Tests\Service\PayPal\MockVerifyWebhookSignatureFactory;
use App\Tests\WebTestCase;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Api\VerifyWebhookSignatureResponse;
use Symfony\Component\Messenger\Transport\TransportInterface;

class PaypalWebhookControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     */
    public function testPartialRefund(): void
    {
        $verifyWebhookSignature = static::$container->get(MockVerifyWebhookSignatureFactory::class);

        $mock = $this->getMockBuilder(VerifyWebhookSignature::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())->method('post')->willReturn(new VerifyWebhookSignatureResponse(['verification_status' => 'SUCCESS']));
        $verifyWebhookSignature->setVerifyWebhookSignature($mock);

        $paypalHttpClient = static::$container->get(MockPayPalHttpClient::class);
        $paypalHttpClient->setGetResponse('1154f530-dbb5-425d-94e8-9a3200b75e35', '51.33', '49.83', '0daa10b329a9', '20.50', '19.40', true);

        /** @var Funding $funding */
        $funding = $this->doctrine->getRepository(Funding::class)->find('1154f530-dbb5-425d-94e8-9a3200b75e35');
        static::assertSame(5133, $funding->getUser()->getCoins());

        $this->client->xmlHttpRequest('POST', '/api/funding/paypal-webhook', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
            'resource' => [
                'custom_id' => '1154f530-dbb5-425d-94e8-9a3200b75e35',
            ],
        ]));

        static::assertSame(204, $this->client->getResponse()->getStatusCode());

        $funding = $this->doctrine->getRepository(Funding::class)->find('1154f530-dbb5-425d-94e8-9a3200b75e35');
        static::assertSame('PARTIALLY_REFUNDED', $funding->getPaypalStatus());
        static::assertSame(2050, $funding->getRefundedAmount());
        static::assertSame(1940, $funding->getRefundedNetAmount());
        static::assertArraySubset([
            'payments' => [
                'captures' => [
                    [
                        'status' => 'PARTIALLY_REFUNDED',
                        'seller_receivable_breakdown' => [
                            'total_refunded_amount' => [
                                'currency_code' => 'USD',
                                'value' => '20.50',
                            ],
                        ],
                    ],
                ],
                'refunds' => [
                    [
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => '20.50',
                        ],
                        'seller_payable_breakdown' => [
                            'gross_amount' => [
                                'currency_code' => 'USD',
                                'value' => '20.50',
                            ],
                            'paypal_fee' => [
                                'currency_code' => 'USD',
                                'value' => '1.10',
                            ],
                            'net_amount' => [
                                'currency_code' => 'USD',
                                'value' => '19.40',
                            ],
                        ],
                        'custom_id' => '1154f530-dbb5-425d-94e8-9a3200b75e35',
                    ],
                ],
            ],
        ], $funding->getPaypalPurchase());
        static::assertSame(5133 - 2050, $funding->getUser()->getCoins()); // removed X coins

        /** @var TransportInterface $transport */
        $transport = static::$container->get('messenger.transport.sync');
        $envelopes = $transport->get();
        static::assertCount(1, $envelopes);
        static::assertInstanceOf(SendOrderRefundMail::class, $envelopes[0]->getMessage());
        static::assertSame($funding->getId()->toString(), $envelopes[0]->getMessage()->getFundingId()->toString());
    }

    /**
     * @group functional
     * @group funding
     */
    public function testMultipleFullRefund(): void
    {
        $verifyWebhookSignature = static::$container->get(MockVerifyWebhookSignatureFactory::class);

        $mock = $this->getMockBuilder(VerifyWebhookSignature::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())->method('post')->willReturn(new VerifyWebhookSignatureResponse(['verification_status' => 'SUCCESS']));
        $verifyWebhookSignature->setVerifyWebhookSignature($mock);

        $paypalHttpClient = static::$container->get(MockPayPalHttpClient::class);
        $paypalHttpClient->setGetResponse('fc296ec6-0e4f-405e-a44c-05e089428751', '12.00', '10.85', '9780f6f97c6e', '5.00', '4.20', true);
        $paypalHttpClient->addRefund('fc296ec6-0e4f-405e-a44c-05e089428751', '543e47dbd14e', '7.00', '6.80');

        /** @var Funding $funding */
        $funding = $this->doctrine->getRepository(Funding::class)->find('fc296ec6-0e4f-405e-a44c-05e089428751');
        static::assertSame(700, $funding->getUser()->getCoins());

        $this->client->xmlHttpRequest('POST', '/api/funding/paypal-webhook', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'event_type' => 'PAYMENT.CAPTURE.REFUNDED',
            'resource' => [
                'id' => '543e47dbd14e',
                'custom_id' => 'fc296ec6-0e4f-405e-a44c-05e089428751',
            ],
        ]));

        static::assertSame(204, $this->client->getResponse()->getStatusCode());

        $funding = $this->doctrine->getRepository(Funding::class)->find('fc296ec6-0e4f-405e-a44c-05e089428751');
        static::assertSame('REFUNDED', $funding->getPaypalStatus());
        static::assertSame(1200, $funding->getRefundedAmount());
        static::assertSame(1100, $funding->getRefundedNetAmount());
        static::assertArraySubset([
            'payments' => [
                'captures' => [
                    [
                        'status' => 'REFUNDED',
                        'seller_receivable_breakdown' => [
                            'total_refunded_amount' => [
                                'currency_code' => 'USD',
                                'value' => '12.00',
                            ],
                        ],
                    ],
                ],
                'refunds' => [
                    [
                        'id' => '9780f6f97c6e',
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => '5.00',
                        ],
                        'seller_payable_breakdown' => [
                            'gross_amount' => [
                                'currency_code' => 'USD',
                                'value' => '5.00',
                            ],
                            'paypal_fee' => [
                                'currency_code' => 'USD',
                                'value' => '0.80',
                            ],
                            'net_amount' => [
                                'currency_code' => 'USD',
                                'value' => '4.20',
                            ],
                        ],
                        'custom_id' => 'fc296ec6-0e4f-405e-a44c-05e089428751',
                    ],
                    [
                        'id' => '543e47dbd14e',
                        'status' => 'COMPLETED',
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => '7.00',
                        ],
                        'seller_payable_breakdown' => [
                            'gross_amount' => [
                                'currency_code' => 'USD',
                                'value' => '7.00',
                            ],
                            'paypal_fee' => [
                                'currency_code' => 'USD',
                                'value' => '0.20',
                            ],
                            'net_amount' => [
                                'currency_code' => 'USD',
                                'value' => '6.80',
                            ],
                        ],
                        'custom_id' => 'fc296ec6-0e4f-405e-a44c-05e089428751',
                    ],
                ],
            ],
        ], $funding->getPaypalPurchase());
        static::assertSame(700 - 700, $funding->getUser()->getCoins()); // no more coins

        /** @var TransportInterface $transport */
        $transport = static::$container->get('messenger.transport.sync');
        $envelopes = $transport->get();
        static::assertCount(1, $envelopes);
        static::assertInstanceOf(SendOrderRefundMail::class, $envelopes[0]->getMessage());
        static::assertSame($funding->getId()->toString(), $envelopes[0]->getMessage()->getFundingId()->toString());
    }

    /**
     * @group functional
     * @group funding
     */
    public function testDeny(): void
    {
        $verifyWebhookSignature = static::$container->get(MockVerifyWebhookSignatureFactory::class);

        $mock = $this->getMockBuilder(VerifyWebhookSignature::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())->method('post')->willReturn(new VerifyWebhookSignatureResponse(['verification_status' => 'SUCCESS']));
        $verifyWebhookSignature->setVerifyWebhookSignature($mock);

        $paypalHttpClient = static::$container->get(MockPayPalHttpClient::class);
        $paypalHttpClient->setGetResponseDeny('1154f530-dbb5-425d-94e8-9a3200b75e35', '51.33', '49.83');

        /** @var Funding $funding */
        $funding = $this->doctrine->getRepository(Funding::class)->find('1154f530-dbb5-425d-94e8-9a3200b75e35');
        static::assertSame(5133, $funding->getUser()->getCoins());

        $this->client->xmlHttpRequest('POST', '/api/funding/paypal-webhook', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'event_type' => 'PAYMENT.CAPTURE.DENIED',
            'resource' => [
                'custom_id' => '1154f530-dbb5-425d-94e8-9a3200b75e35',
            ],
        ]));

        static::assertSame(204, $this->client->getResponse()->getStatusCode());

        $funding = $this->doctrine->getRepository(Funding::class)->find('1154f530-dbb5-425d-94e8-9a3200b75e35');
        static::assertSame('DENIED', $funding->getPaypalStatus());
        static::assertArraySubset([
            'payments' => [
                'captures' => [
                    [
                        'status' => 'DENIED',
                    ],
                ],
            ],
        ], $funding->getPaypalPurchase());
        static::assertSame(5133 - 5133, $funding->getUser()->getCoins()); // coins canceled
    }

    /**
     * @group functional
     * @group funding
     */
    public function testBadSignature(): void
    {
        $verifyWebhookSignature = static::$container->get(MockVerifyWebhookSignatureFactory::class);

        $mock = $this->getMockBuilder(VerifyWebhookSignature::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mock->expects($this->once())->method('post')->willReturn(new VerifyWebhookSignatureResponse(['verification_status' => 'FAILURE']));
        $verifyWebhookSignature->setVerifyWebhookSignature($mock);

        $this->client->xmlHttpRequest('POST', '/api/funding/paypal-webhook', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('bad signature.', $json['error']);
    }
}
