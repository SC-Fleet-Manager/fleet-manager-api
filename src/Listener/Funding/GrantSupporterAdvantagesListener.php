<?php

namespace App\Listener\Funding;

use App\Event\FundingCapturedEvent;

class GrantSupporterAdvantagesListener
{
    public function __invoke(FundingCapturedEvent $event): void
    {
        $funding = $event->getFunding();

        $user = $funding->getUser();
        if ($user === null) {
            return;
        }

        $amount = $funding->getEffectiveAmount();
        $user->addCoins($amount);
    }
}
