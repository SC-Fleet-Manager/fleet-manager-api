<?php

namespace App\Listener\Funding;

use App\Event\FundingCapturedEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GrantSupporterAdvantagesListener implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __invoke(FundingCapturedEvent $event): void
    {
        $funding = $event->getFunding();

        $user = $funding->getUser();
        if ($user === null) {
            return;
        }

        $amount = $funding->getEffectiveAmount();
        $user->addCoins($amount);

//        $user->removeCoins();
    }
}
