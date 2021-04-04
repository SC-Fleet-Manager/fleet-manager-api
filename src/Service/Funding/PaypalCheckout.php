<?php

namespace App\Service\Funding;

use App\Entity\Funding;
use App\Entity\User;
use App\Exception\UnableToCreatePaypalOrderException;
use PayPal\Api\VerifyWebhookSignatureResponse;
use PayPal\Rest\ApiContext;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use PayPalHttp\HttpClient;
use PayPalHttp\HttpException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;

class PaypalCheckout implements PaypalCheckoutInterface
{
//    private UrlGeneratorInterface $urlGenerator;
    private LoggerInterface $fundingLogger;
    private HttpClient $client;
    private ApiContext $apiContext;
    private VerifyWebhookSignatureFactory $verifyWebhookSignatureFactory;

    public function __construct(
        /*UrlGeneratorInterface $urlGenerator, */
        LoggerInterface $fundingLogger,
        PayPalHttpClient $client,
        ApiContext $apiContext,
        VerifyWebhookSignatureFactory $verifyWebhookSignatureFactory
    ) {
//        $this->urlGenerator = $urlGenerator;
        $this->fundingLogger = $fundingLogger;
        $this->client = $client;
        $this->apiContext = $apiContext;
        $this->verifyWebhookSignatureFactory = $verifyWebhookSignatureFactory;
    }

    public function create(Funding $funding, User $user, string $locale): void
    {
        $amountValue = bcdiv($funding->getAmount(), 100, 2);

        /** @see https://developer.paypal.com/docs/api/orders/v2/#orders_create */
        $orderRequest = new OrdersCreateRequest();
        $orderRequest->prefer('return=representation');
        $orderRequest->body = [
            'intent' => 'CAPTURE',
            'payer' => [
                'email_address' => null,
            ],
            'application_context' => [
                'locale' => str_replace('_', '-', $locale),
                'landing_page' => 'BILLING',
                'shipping_preferences' => 'NO_SHIPPING', // for digital purchases
                'user_action' => 'PAY_NOW',
//                'return_url' => $this->urlGenerator->generate('funding_paypal_return', [], UrlGeneratorInterface::ABSOLUTE_URL),
//                'cancel_url' => $this->urlGenerator->generate('funding_paypal_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ],
            'purchase_units' => [
                [
                    'reference_id' => self::BACKING_REFID,
                    'description' => 'Fleet Manager backing',
                    'custom_id' => (string) $funding->getId(),
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => $amountValue,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'USD',
                                'value' => $amountValue,
                            ],
                        ],
                    ],
                    'items' => [
                        [
                            'name' => 'Fleet Manager backing',
                            'unit_amount' => [
                                'currency_code' => 'USD',
                                'value' => $amountValue,
                            ],
                            'quantity' => 1,
                            'category' => 'DIGITAL_GOODS',
                        ],
                    ],
                ],
            ],
        ];

        try {
            $response = $this->client->execute($orderRequest);
        } catch (\Throwable $e) {
            $error = json_decode($e->getMessage(), true);
            $this->fundingLogger->error('[Create Order] An error has occurred when creating an order on PayPal.', ['exception' => $e, 'error' => $error]);

            throw new UnableToCreatePaypalOrderException($error, 'An error has occurred when submitting the backing.');
        }

        if ($response->statusCode >= 400) {
            $this->fundingLogger->error('[Create Order] Unable to create the order on PayPal.', ['response' => $response]);

            throw new \LogicException('Unable to create an order on PayPal.');
        }

        $this->fundingLogger->info('[Create Order] An order has been created.', ['response' => $response, 'fundingId' => $funding->getId()]);

        $funding->setPaypalOrderId($response->result->id);
        $funding->setPaypalStatus($response->result->status);
        $funding->setCreatedAt(new \DateTimeImmutable($response->result->create_time));
    }

    public function capture(Funding $funding): void
    {
        /** @see https://developer.paypal.com/docs/api/orders/v2/#orders_capture */
        $orderRequest = new OrdersCaptureRequest($funding->getPaypalOrderId());

        try {
            $response = $this->client->execute($orderRequest);
        } catch (HttpException $e) {
            if (!isset($e->headers['Content-Type'])) {
                $this->fundingLogger->error('[Capture Order] An error has occurred when capturing an order on PayPal.', ['exception' => $e, 'fundingId' => $funding->getId()]);
                throw new \LogicException('Unable to capture an order on PayPal.');
            }
            $error = json_decode($e->getMessage(), true);
            $this->fundingLogger->error('[Capture Order] An error has occurred when capturing an order on PayPal.', ['exception' => $e, 'error' => $error, 'fundingId' => $funding->getId()]);

            throw new UnableToCreatePaypalOrderException($error, 'An error has occurred when validating the backing.');
        }

        if ($response->statusCode >= 400) {
            $this->fundingLogger->error('[Capture Order] Unable to capture the order on PayPal.', ['response' => $response, 'fundingId' => $funding->getId()]);

            throw new \LogicException('Unable to capture the order on PayPal.');
        }

        $this->fundingLogger->info('[Capture Order] An order has been captured.', ['response' => $response, 'fundingId' => $funding->getId()]);

        foreach ($response->result->purchase_units as $purchaseUnit) {
            if ($purchaseUnit->reference_id !== self::BACKING_REFID) {
                continue;
            }
            $funding->setPaypalPurchase($this->transformPurchase($purchaseUnit));
            foreach ($purchaseUnit->payments->captures as $paymentCapture) {
                if ($paymentCapture->custom_id !== (string) $funding->getId()) {
                    continue;
                }

                $funding->setPaypalStatus($paymentCapture->status);
                $netAmount = $paymentCapture->seller_receivable_breakdown->net_amount->value ?? null;
                if ($netAmount !== null) {
                    $netAmount = (int) bcmul($netAmount, 100);
                }
                $funding->setNetAmount($netAmount ?? $funding->getAmount());
            }
        }

        // TODO : send email with $response->payer->email_address
    }

    public function complete(Funding $funding): void
    {
        $this->refreshOrder($funding);

        if ($funding->getPaypalStatus() === 'COMPLETED') {
            $this->fundingLogger->info('[Capture Order] An order has been completed.', ['fundingId' => $funding->getId()]);

            // TODO : send email with $response->payer->email_address
        }
    }

    public function refund(Funding $funding): void
    {
        $this->refreshOrder($funding);

        if ($funding->getPaypalStatus() === 'REFUNDED' || $funding->getPaypalStatus() === 'PARTIALLY_REFUNDED') {
            $this->fundingLogger->info('[Capture Order] An order has been (partially) refunded.', ['fundingId' => $funding->getId()]);

            // TODO : send email with $response->payer->email_address
        }
    }

    public function deny(Funding $funding): void
    {
        $this->refreshOrder($funding);

        if ($funding->getPaypalStatus() === 'DENIED') {
            $this->fundingLogger->info('[Capture Order] An order has been denied.', ['fundingId' => $funding->getId()]);

            // TODO : send email with $response->payer->email_address
        }
    }

    public function refreshOrder(Funding $funding): void
    {
        $orderRequest = new OrdersGetRequest($funding->getPaypalOrderId());

        try {
            $response = $this->client->execute($orderRequest);
        } catch (HttpException $exception) {
            // TODO : use MOM!
            $this->fundingLogger->error('[Get Order] Unable to get the order from PayPal.', ['body' => $exception->getMessage(), 'statusCode' => $exception->statusCode, 'exception' => $exception]);

            return;
        }

        foreach ($response->result->purchase_units as $purchaseUnit) {
            if ($purchaseUnit->reference_id !== self::BACKING_REFID) {
                continue;
            }
            $funding->setPaypalPurchase($this->transformPurchase($purchaseUnit));
            if (isset($purchaseUnit->payments->captures)) {
                foreach ($purchaseUnit->payments->captures as $paymentCapture) {
                    if ($paymentCapture->custom_id !== (string) $funding->getId()) {
                        continue;
                    }
                    $funding->setPaypalStatus($paymentCapture->status);
                    $netAmount = $paymentCapture->seller_receivable_breakdown->net_amount->value ?? null;
                    if ($netAmount !== null) {
                        $netAmount = (int) bcmul($netAmount, 100);
                    }
                    $funding->setNetAmount($netAmount ?? $funding->getAmount());
                }
            }
            if (isset($purchaseUnit->payments->refunds)) {
                $refundedAmount = 0;
                $refundedNetAmount = 0;
                $refundedAt = null;
                foreach ($purchaseUnit->payments->refunds as $paymentRefund) {
                    $refundedAmount += (int) bcmul($paymentRefund->seller_payable_breakdown->gross_amount->value, 100);
                    $refundedNetAmount += (int) bcmul($paymentRefund->seller_payable_breakdown->net_amount->value, 100);
                    $createTime = new \DateTimeImmutable($paymentRefund->create_time);
                    if ($refundedAt === null || $createTime < $refundedAt) {
                        $refundedAt = $createTime;
                    }
                }
                $funding->setRefundedAmount($refundedAmount);
                $funding->setRefundedNetAmount($refundedNetAmount);
                $funding->setRefundedAt($refundedAt);
            }
        }
    }

    public function verifySignature(Request $request): bool
    {
        $signatureVerification = $this->verifyWebhookSignatureFactory->createVerifyWebhookSignature($request);

        try {
            /** @var VerifyWebhookSignatureResponse $output */
            $output = $signatureVerification->post($this->apiContext);
            if ($output->getVerificationStatus() !== 'SUCCESS') {
                $this->fundingLogger->error('[Webhook] Bad signature.', ['headers' => $request->headers->all()]);

                return false;
            }
        } catch (\Exception $e) {
            $this->fundingLogger->error('[Webhook] Unable to verify webhook signature.', ['exception' => $e]);

            throw $e;
        }

        return true;
    }

    private function transformPurchase(object $purchaseUnit): array
    {
        $purchase = json_decode(json_encode($purchaseUnit), true);
        unset($purchase['shipping']); // RGPD

        return $purchase;
    }
}
