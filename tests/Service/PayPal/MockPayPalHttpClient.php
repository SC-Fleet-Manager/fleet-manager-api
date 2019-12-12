<?php

namespace App\Tests\Service\PayPal;

use App\Service\Funding\PaypalCheckoutInterface;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpRequest;

class MockPayPalHttpClient extends PayPalHttpClient
{
    private array $createResponse;
    private array $captureResponse;

    public function __construct()
    {
        parent::__construct(new SandboxEnvironment('', ''));
        $this->setCreateResponse('123456ABCDEF', 'CREATED', '2019-12-11T20:22:33.778Z');
        $this->setCaptureResponse('4f33b118-70eb-4a01-9161-5b70056c6f92', '1.00', '0.67');
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
                                            'value' => (string) ((float) $amountValue - (float) $netAmount),
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

    public function execute(HttpRequest $httpRequest): ?object
    {
        if ($httpRequest instanceof OrdersCreateRequest) {
            return json_decode(json_encode($this->createResponse), false);
        }
        if ($httpRequest instanceof OrdersCaptureRequest) {
            return json_decode(json_encode($this->captureResponse), false);
        }

        return null;
    }
}
