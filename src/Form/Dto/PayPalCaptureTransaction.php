<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PayPalCaptureTransaction
{
    /** e.g. "56L60734SJ740973U" */
    public ?string $orderID;
    /** e.g. "RFLQPZK68JF6U" */
    public ?string $payerID;
    public ?string $paymentID;
}
