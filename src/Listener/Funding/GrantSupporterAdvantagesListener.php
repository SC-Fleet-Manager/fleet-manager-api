<?php

namespace App\Listener\Funding;

use App\Event\FundingCapturedEvent;

class GrantSupporterAdvantagesListener
{
    public function __construct()
    {
    }

    public function __invoke(FundingCapturedEvent $event): void
    {
        $funding = $event->getFunding();
    }
}
