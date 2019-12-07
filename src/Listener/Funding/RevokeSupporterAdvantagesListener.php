<?php

namespace App\Listener\Funding;

use App\Event\FundingRefundedEvent;

class RevokeSupporterAdvantagesListener
{
    public function __invoke(FundingRefundedEvent $event): void
    {
        $funding = $event->getFunding();

        $user = $funding->getUser();
        if ($user === null) {
            return;
        }

        $user->removeCoins($funding->getRefundedAmount());
    }
}
