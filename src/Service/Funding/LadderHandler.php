<?php

namespace App\Service\Funding;

use App\Entity\User;
use App\Repository\FundingRepository;
use App\Service\Funding\Dto\LadderView;
use Symfony\Component\Security\Core\Security;

class LadderHandler
{
    private Security $security;
    private FundingRepository $fundingRepository;

    public function __construct(Security $security, FundingRepository $fundingRepository)
    {
        $this->security = $security;
        $this->fundingRepository = $fundingRepository;
    }

    public function getAlltimeLadder(): array
    {
        return $this->buildLadder($this->fundingRepository->getAlltimeLadder());
    }

    public function getMonthlyLadder(): array
    {
        $month = new \DateTimeImmutable('first day of');

        return $this->buildLadder($this->fundingRepository->getMonthlyLadder($month), $month);
    }

    /**
     * @return LadderView[]
     */
    private function buildLadder(array $topFundings, ?\DateTimeInterface $month = null): array
    {
        /** @var User|null $user */
        $user = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->security->getUser();
        }

        $viewFundings = [];
        $addMyRank = true;
        $rank = 1;
        $sequence = 1;
        $lastAmount = null;
        foreach ($topFundings as $topFunding) {
            if ($lastAmount !== null && $lastAmount !== $topFunding['totalAmount']) {
                $rank = $sequence;
            }
            $lastAmount = $topFunding['totalAmount'];
            $viewFunding = new LadderView(
                $rank,
                $topFunding['totalAmount'],
                $topFunding['actualHandle'] ?? $topFunding['nickname'] ?? $topFunding['username'],
                $user !== null ? $user->getId()->toString() === $topFunding['userId'] : false,
            );
            if ($viewFunding->me) {
                $addMyRank = false;
            }
            $viewFundings[] = $viewFunding;
            ++$sequence;
        }
        if ($addMyRank) {
            $userRank = null;
            $totalAmount = 0;
            if ($user !== null && $user->hasCoins()) {
                $totalAmount = $this->fundingRepository->getTotalAmountByUser($user->getId(), $month);
                if ($totalAmount > 0) {
                    $userRank = $this->fundingRepository->getRankLadder($totalAmount, $month);
                }
            }
            if ($userRank !== null) {
                $citizen = $user->getCitizen();
                $viewFundings[] = new LadderView(
                    $userRank,
                    $totalAmount,
                    $citizen !== null ? $citizen->getActualHandle()->getHandle() : $user->getNickname() ?? $user->getUsername(),
                    true,
                );
            }
        }

        return $viewFundings;
    }
}
