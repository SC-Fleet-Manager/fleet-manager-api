<?php

namespace App\Tests\Service\PayPal;

use App\Service\Funding\VerifyWebhookSignatureFactory;
use PayPal\Api\VerifyWebhookSignature;
use Symfony\Component\HttpFoundation\Request;

class MockVerifyWebhookSignatureFactory extends VerifyWebhookSignatureFactory
{
    private VerifyWebhookSignature $verifyWebhookSignature;

    public function __construct()
    {
        parent::__construct('');
        $this->verifyWebhookSignature = new VerifyWebhookSignature();
    }

    public function setVerifyWebhookSignature(VerifyWebhookSignature $verifyWebhookSignature): void
    {
        $this->verifyWebhookSignature = $verifyWebhookSignature;
    }

    public function createVerifyWebhookSignature(Request $request): VerifyWebhookSignature
    {
        return $this->verifyWebhookSignature;
    }
}
