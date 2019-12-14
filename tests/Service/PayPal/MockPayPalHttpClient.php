<?php

namespace App\Tests\Service\PayPal;

use App\Service\Funding\PaypalCheckoutInterface;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpRequest;

class MockPayPalHttpClient extends PayPalHttpClient
{
    private array $createResponse;
    private array $captureResponse;
    private array $getResponse;

    public function __construct()
    {
        parent::__construct(new SandboxEnvironment('', ''));
        $this->setCreateResponse('123456ABCDEF', 'CREATED', '2019-12-11T20:22:33.778Z');
        $this->setCaptureResponse('4f33b118-70eb-4a01-9161-5b70056c6f92', '1.00', '0.67');
        $this->setGetResponse('4f33b118-70eb-4a01-9161-5b70056c6f92', '1.00', '0.67', '77f3c9d706bd', '0.50', '0.10', true);
    }

    public function setCreateResponse(string $id, string $status, string $createTime): void
    {
        $this->createResponse = [
            'statusCode' => 200,
            'result' => [
                'id' => $id,
                'status' => $status,
                'create_time' => $createTime,
            ],
        ];
    }

    public function setCaptureResponse(string $customId, string $amountValue, string $netAmount): void
    {
        $this->captureResponse = [
            'statusCode' => 200,
            'result' => [
                'purchase_units' => [
                    [
                        'reference_id' => PaypalCheckoutInterface::BACKING_REFID,
                        'payments' => [
                            'captures' => [
                                [
                                    'status' => 'COMPLETED',
                                    'amount' => [
                                        'currency_code' => 'USD',
                                        'value' => $amountValue,
                                    ],
                                    'seller_receivable_breakdown' => [
                                        'gross_amount' => [
                                            'currency_code' => 'USD',
                                            'value' => $amountValue,
                                        ],
                                        'paypal_fee' => [
                                            'currency_code' => 'USD',
                                            'value' => number_format((float) $amountValue - (float) $netAmount, 2),
                                        ],
                                        'net_amount' => [
                                            'currency_code' => 'USD',
                                            'value' => $netAmount,
                                        ],
                                    ],
                                    'custom_id' => $customId,
                                    'create_time' => '2019-12-12T20:18:54Z',
                                    'update_time' => '2019-12-12T20:18:54Z',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function setGetResponseDeny(string $customId, string $amountValue, string $netAmount): void
    {
        $this->getResponse = [
            'statusCode' => 200,
            'result' => [
                'purchase_units' => [
                    [
                        'reference_id' => PaypalCheckoutInterface::BACKING_REFID,
                        'payments' => [
                            'captures' => [
                                [
                                    'status' => 'DENIED',
                                    'amount' => [
                                        'currency_code' => 'USD',
                                        'value' => $amountValue,
                                    ],
                                    'seller_receivable_breakdown' => [
                                        'gross_amount' => [
                                            'currency_code' => 'USD',
                                            'value' => $amountValue,
                                        ],
                                        'paypal_fee' => [
                                            'currency_code' => 'USD',
                                            'value' => number_format((float) $amountValue - (float) $netAmount, 2),
                                        ],
                                        'net_amount' => [
                                            'currency_code' => 'USD',
                                            'value' => $netAmount,
                                        ],
                                    ],
                                    'custom_id' => $customId,
                                    'create_time' => '2019-12-12T20:18:54Z',
                                    'update_time' => '2019-12-12T21:18:54Z',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public function addRefund(string $customId, string $refundId, string $refundedAmount, string $refundedNetAmount, bool $partiallyRefunded = false): void
    {
        $this->getResponse['result']['purchase_units'][0]['payments']['captures'][0]['status'] = $partiallyRefunded ? 'PARTIALLY_REFUNDED' : 'REFUNDED';
        $this->getResponse['result']['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['total_refunded_amount'] = [
            'currency_code' => 'USD',
            'value' => number_format((float) $refundedAmount + (float) $this->getResponse['result']['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['total_refunded_amount']['value'], 2),
        ];
        $this->getResponse['result']['purchase_units'][0]['payments']['refunds'][] = [
            'id' => $refundId,
            'status' => 'COMPLETED',
            'amount' => [
                'currency_code' => 'USD',
                'value' => $refundedAmount,
            ],
            'seller_payable_breakdown' => [
                'gross_amount' => [
                    'currency_code' => 'USD',
                    'value' => $refundedAmount,
                ],
                'paypal_fee' => [
                    'currency_code' => 'USD',
                    'value' => number_format((float) $refundedAmount - (float) $refundedNetAmount, 2),
                ],
                'net_amount' => [
                    'currency_code' => 'USD',
                    'value' => $refundedNetAmount,
                ],
            ],
            'custom_id' => $customId,
            'create_time' => '2019-12-12T21:18:54Z',
            'update_time' => '2019-12-12T21:18:54Z',
        ];
    }

    public function setGetResponse(string $customId, string $amountValue, string $netAmount, ?string $refundId = null, ?string $refundedAmount = null, ?string $refundedNetAmount = null, bool $partiallyRefunded = false): void
    {
        $this->getResponse = [
            'statusCode' => 200,
            'result' => [
                'purchase_units' => [
                    [
                        'reference_id' => PaypalCheckoutInterface::BACKING_REFID,
                        'payments' => [
                            'captures' => [
                                [
                                    'status' => $refundId === null ? 'COMPLETED' : ($partiallyRefunded ? 'PARTIALLY_REFUNDED' : 'REFUNDED'),
                                    'amount' => [
                                        'currency_code' => 'USD',
                                        'value' => $amountValue,
                                    ],
                                    'seller_receivable_breakdown' => [
                                        'gross_amount' => [
                                            'currency_code' => 'USD',
                                            'value' => $amountValue,
                                        ],
                                        'paypal_fee' => [
                                            'currency_code' => 'USD',
                                            'value' => number_format((float) $amountValue - (float) $netAmount, 2),
                                        ],
                                        'net_amount' => [
                                            'currency_code' => 'USD',
                                            'value' => $netAmount,
                                        ],
                                    ],
                                    'custom_id' => $customId,
                                    'create_time' => '2019-12-12T20:18:54Z',
                                    'update_time' => '2019-12-12T21:18:54Z',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        if ($refundId !== null) {
            $this->getResponse['result']['purchase_units'][0]['payments']['captures'][0]['seller_receivable_breakdown']['total_refunded_amount'] = [
                'currency_code' => 'USD',
                'value' => $refundedAmount,
            ];
            $this->getResponse['result']['purchase_units'][0]['payments']['refunds'] = [
                [
                    'id' => $refundId,
                    'status' => 'COMPLETED',
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $refundedAmount,
                    ],
                    'seller_payable_breakdown' => [
                        'gross_amount' => [
                            'currency_code' => 'USD',
                            'value' => $refundedAmount,
                        ],
                        'paypal_fee' => [
                            'currency_code' => 'USD',
                            'value' => number_format((float) $refundedAmount - (float) $refundedNetAmount, 2),
                        ],
                        'net_amount' => [
                            'currency_code' => 'USD',
                            'value' => $refundedNetAmount,
                        ],
                    ],
                    'custom_id' => $customId,
                    'create_time' => '2019-12-12T21:18:54Z',
                    'update_time' => '2019-12-12T21:18:54Z',
                ],
            ];
        }
    }

    public function execute(HttpRequest $httpRequest): ?object
    {
        if ($httpRequest instanceof OrdersCreateRequest) {
            return json_decode(json_encode($this->createResponse), false);
        }
        if ($httpRequest instanceof OrdersCaptureRequest) {
            return json_decode(json_encode($this->captureResponse), false);
        }
        if ($httpRequest instanceof OrdersGetRequest) {
            return json_decode(json_encode($this->getResponse), false);
        }

        return null;
    }
}
