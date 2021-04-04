<?php

namespace App\Message\Funding;

use App\Domain\FundingId;

class SendOrderRefundMail
{
    public function __construct(
        private FundingId $fundingId
    ) {
    }

    public function getFundingId(): FundingId
    {
        return $this->fundingId;
    }
}
