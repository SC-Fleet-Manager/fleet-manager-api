<?php

namespace App\Service\Funding;

use PayPal\Api\VerifyWebhookSignature;
use Symfony\Component\HttpFoundation\Request;

class VerifyWebhookSignatureFactory
{
    private string $webhookId;

    public function __construct(string $webhookId)
    {
        $this->webhookId = $webhookId;
    }

    public function createVerifyWebhookSignature(Request $request): VerifyWebhookSignature
    {
        $signatureVerification = new VerifyWebhookSignature();
        $signatureVerification->setAuthAlgo($request->headers->get('paypal-auth-algo'));
        $signatureVerification->setTransmissionId($request->headers->get('paypal-transmission-id'));
        $signatureVerification->setCertUrl($request->headers->get('paypal-cert-url'));
        $signatureVerification->setWebhookId($this->webhookId);
        $signatureVerification->setTransmissionSig($request->headers->get('paypal-transmission-sig'));
        $signatureVerification->setTransmissionTime($request->headers->get('paypal-transmission-time'));
        $signatureVerification->setRequestBody($request->getContent());

        return $signatureVerification;
    }
}
