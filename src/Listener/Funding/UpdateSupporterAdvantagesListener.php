<?php

namespace App\Listener\Funding;

use App\Entity\Funding;
use App\Event\FundingUpdatedEvent;
use App\Repository\FundingRepository;
use Doctrine\ORM\EntityManagerInterface;

class UpdateSupporterAdvantagesListener
{
    private FundingRepository $fundingRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(FundingRepository $fundingRepository, EntityManagerInterface $entityManager)
    {
        $this->fundingRepository = $fundingRepository;
        $this->entityManager = $entityManager;
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
        }
        $user->setCoins($balance);
        $this->entityManager->flush();
    }
}
