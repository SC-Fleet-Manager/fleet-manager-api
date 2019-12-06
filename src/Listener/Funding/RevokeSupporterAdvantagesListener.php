<?php

namespace App\Listener\Funding;

use App\Event\FundingRefundedEvent;

class RevokeSupporterAdvantagesListener
{
    public function __construct()
    {
    }

    public function __invoke(FundingRefundedEvent $event): void
    {
        $funding = $event->getFunding();
    }
}
