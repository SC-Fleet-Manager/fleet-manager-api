<?php

namespace App\Message\Funding;

use Ramsey\Uuid\UuidInterface;

class SendOrderCaptureSummaryMail
{
    private UuidInterface $fundingId;

    public function __construct(UuidInterface $fundingId)
    {
        $this->fundingId = $fundingId;
    }

    public function getFundingId(): UuidInterface
    {
        return $this->fundingId;
    }
}
