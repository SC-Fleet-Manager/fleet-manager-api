<?php

namespace App\Form\Dto;

class FundingRefund
{
    public \DateTimeInterface $createdAt;
    public int $refundedAmount;
    public string $currency;

    public function __construct(\DateTimeInterface $createdAt, int $refundedAmount, string $currency)
    {
        $this->createdAt = $createdAt;
        $this->refundedAmount = $refundedAmount;
        $this->currency = $currency;
    }
}
