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

    public function getAlltimeOrgaLadder(): array
    {
        return $this->buildOrgaLadder($this->fundingRepository->getAlltimeOrgaLadder());
    }

    public function getMonthlyOrgaLadder(): array
    {
        $month = new \DateTimeImmutable('first day of');

        return $this->buildOrgaLadder($this->fundingRepository->getMonthlyOrgaLadder($month));
    }

    private function buildOrgaLadder(array $topFundings): array
    {
        $viewFundings = [];
        $rank = 1;
        $sequence = 1;
        $lastAmount = null;
        foreach ($topFundings as $topFunding) {
            if ($lastAmount !== null && $lastAmount !== $topFunding['totalAmount']) {
                $rank = $sequence;
            }
            $lastAmount = $topFunding['totalAmount'];
            $name = $topFunding['orgaName'] ?? $topFunding['sid'] ?? 'Unknown';
            if (!($topFunding['supporterVisible'] ?? true)) {
                $name = 'Anonymous';
            }
            $viewFunding = new LadderView(
                $rank,
                $topFunding['totalAmount'],
                $name,
            );
            $viewFundings[] = $viewFunding;
            ++$sequence;
        }

        return $viewFundings;
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
            $name = $topFunding['actualHandle'] ?? $topFunding['nickname'] ?? 'Unknown';
            if (!($topFunding['supporterVisible'] ?? true)) {
                $name = 'Anonymous';
            }
            $viewFunding = new LadderView(
                $rank,
                $topFunding['totalAmount'],
                $name,
                $topFunding['orgaName'],
                $topFunding['avatarUrl'],
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
                $name = $citizen !== null ? $citizen->getActualHandle()->getHandle() : $user->getNickname() ?? $user->getUsername();
                if (!$user->isSupporterVisible()) {
                    $name = 'Anonymous';
                }
                $viewFundings[] = new LadderView(
                    $userRank,
                    $totalAmount,
                    $name,
                    $citizen->getMainOrga() !== null ? $citizen->getMainOrga()->getOrganization()->getName() : null,
                    $citizen->getAvatarUrl(),
                    true,
                );
            }
        }

        return $viewFundings;
    }
}
