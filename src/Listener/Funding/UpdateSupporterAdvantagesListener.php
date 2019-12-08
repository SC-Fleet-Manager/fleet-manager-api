<?php

namespace App\Listener\Funding;

use App\Entity\Funding;
use App\Event\FundingUpdatedEvent;
use App\Repository\FundingRepository;

class UpdateSupporterAdvantagesListener
{
    private FundingRepository $fundingRepository;

    public function __construct(FundingRepository $fundingRepository)
    {
        $this->fundingRepository = $fundingRepository;
    }

    public function __invoke(FundingUpdatedEvent $event): void
    {
        $updatedFunding = $event->getFunding();

        $user = $updatedFunding->getUser();
        if ($user === null) {
            return;
        }

        // recompute all FM Coins balance
        /** @var Funding[] $fundings */
        $fundings = $this->fundingRepository->findBy(['user' => $user, 'paypalStatus' => ['COMPLETED', 'PARTIALLY_REFUNDED']]);
        $balance = 0;
        foreach ($fundings as $funding) {
            $balance += $funding->getEffectiveAmount();
            dump($funding, $balance);
        }
        $user->setCoins($balance);
    }
}
