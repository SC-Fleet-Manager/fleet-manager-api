<?php

namespace App\Message\Funding;

use Ramsey\Uuid\UuidInterface;

class SendOrderCaptureSummaryMail
{
    public function __construct(
        private UuidInterface $fundingId
    ) {
    }

    public function getFundingId(): UuidInterface
    {
        return $this->fundingId;
    }
}
