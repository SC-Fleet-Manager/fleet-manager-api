<?php

namespace App\Service\Funding;

use App\Entity\Funding;
use App\Entity\User;
use App\Form\Dto\FundingRefund;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Api\VerifyWebhookSignatureResponse;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use PayPalCheckoutSdk\Core\PayPalEnvironment;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalHttp\HttpClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaypalCheckout
{
    public const MODE_PROD = 'prod';
    public const MODE_SANDBOX = 'sandbox';

    public const BACKING_REFID = '1a454096-31bc-47cb-9400-07b752809220';

    private UrlGeneratorInterface $urlGenerator;
    private LoggerInterface $fundingLogger;
    private string $clientId;
    private string $clientSecret;
    private string $mode;
    private HttpClient $client;
    private ApiContext $apiContext;

    public function __construct(UrlGeneratorInterface $urlGenerator, LoggerInterface $fundingLogger, string $clientId, string $clientSecret, string $mode = self::MODE_SANDBOX)
    {
        $this->urlGenerator = $urlGenerator;
        $this->fundingLogger = $fundingLogger;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->mode = $mode;

        $this->client = new PayPalHttpClient($this->createEnvironment());
        $this->apiContext = new ApiContext(new OAuthTokenCredential($clientId, $clientSecret));
        $this->apiContext->setConfig([
            'mode' => $mode === self::MODE_PROD ? 'live' : 'sandbox',
        ]);
    }

    public function create(Funding $funding, User $user, string $locale): void
    {
        /** @see https://developer.paypal.com/docs/api/orders/v2/#orders_create */
        $orderRequest = new OrdersCreateRequest();
        $orderRequest->prefer('return=representation');
        $orderRequest->body = [
            'intent' => 'CAPTURE',
            'payer' => [
                'email_address' => $user->getEmail(),
            ],
            'application_context' => [
                'locale' => str_replace('_', '-', $locale),
                'landing_page' => 'BILLING',
                'shipping_preferences' => 'NO_SHIPPING', // digital purchase
                'user_action' => 'PAY_NOW',
//                'return_url' => $this->urlGenerator->generate('funding_paypal_return', [], UrlGeneratorInterface::ABSOLUTE_URL),
//                'cancel_url' => $this->urlGenerator->generate('funding_paypal_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ],
            'purchase_units' => [
                [
                    'reference_id' => self::BACKING_REFID,
                    'description' => 'Fleet Manager backing',
                    'custom_id' => $funding->getId()->toString() ?? '',
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => bcdiv($funding->getAmount(), 100, 2),
                    ],
                ],
            ],
        ];
        $response = $this->client->execute($orderRequest);

        if ($response->statusCode >= 400) {
            $this->fundingLogger->error('[Create Order] Unable to create the order on PayPal.', ['response' => $response]);
            throw new \LogicException('Unable to create an order on PayPal.');
        }

        $funding->setPaypalOrderId($response->result->id);
        $funding->setPaypalStatus($response->result->status);
        $funding->setCreatedAt(new \DateTimeImmutable($response->result->create_time));
    }

    public function capture(Funding $funding): void
    {
        /** @see https://developer.paypal.com/docs/api/orders/v2/#orders_capture */
        $orderRequest = new OrdersCaptureRequest($funding->getPaypalOrderId());
        $response = $this->client->execute($orderRequest);

        if ($response->statusCode >= 400) {
            $this->fundingLogger->error('[Capture Order] Unable to capture the order on PayPal.', ['response' => $response]);
            throw new \LogicException('Unable to capture the order on PayPal.');
        }

        foreach ($response->result->purchase_units as $purchaseUnit) {
            if ($purchaseUnit->reference_id !== self::BACKING_REFID) {
                continue;
            }
            foreach ($purchaseUnit->payments->captures as $paymentCapture) {
                if ($paymentCapture->custom_id !== $funding->getId()->toString()) {
                    continue;
                }
                $funding->setPaypalCapture(json_decode(json_encode($paymentCapture), true));
            }
        }
        $funding->setPaypalStatus($response->result->status);
        if (null !== $capture = $funding->getPaypalCapture()) {
            $netAmount = $capture['seller_receivable_breakdown']['net_amount']['value'] ?? null;
            if ($netAmount !== null) {
                $netAmount = (int) bcmul($netAmount, 100);
            }
            $funding->setNetAmount($netAmount ?? $funding->getAmount());
        }
    }

    public function refund(Funding $funding, FundingRefund $refund): void
    {
        dump($funding, $refund);
        if ($funding->getPaypalStatus() === Funding::STATUS_REFUNDED) {
            return;
        }

        $funding->setPaypalStatus(Funding::STATUS_REFUNDED);
        $funding->setRefundedAmount($refund->refundedAmount);
        $funding->setRefundedAt(clone $refund->createdAt);
    }

    public function verifySignature(Request $request): bool
    {
        $signatureVerification = new VerifyWebhookSignature();
        $signatureVerification->setAuthAlgo($request->headers->get('paypal-auth-algo'));
        $signatureVerification->setTransmissionId($request->headers->get('paypal-transmission-id'));
        $signatureVerification->setCertUrl($request->headers->get('paypal-cert-url'));
        $signatureVerification->setWebhookId('72Y11928S1077440L');
        $signatureVerification->setTransmissionSig($request->headers->get('paypal-transmission-sig'));
        $signatureVerification->setTransmissionTime($request->headers->get('paypal-transmission-time'));
        $signatureVerification->setRequestBody($request->getContent());

        try {
            /** @var VerifyWebhookSignatureResponse $output */
            $output = $signatureVerification->post($this->apiContext);
            if ($output !== 'SUCCESS') {
                $this->fundingLogger->error('[Webhook] Bad signature.', ['headers' => $request->headers->all()]);

                return false;
            }
        } catch (\Exception $e) {
            $this->fundingLogger->error('[Webhook] Unable to verify webhook signature.', ['exception' => $e]);

            throw $e;
        }

        return true;
    }

    private function createEnvironment(): PayPalEnvironment
    {
        if ($this->mode === self::MODE_PROD) {
            return new ProductionEnvironment($this->clientId, $this->clientSecret);
        }

        return new SandboxEnvironment($this->clientId, $this->clientSecret);
    }
}
