<?php

namespace App\Controller\Funding;

use App\Entity\MonthlyCostCoverage;
use App\Repository\FundingRepository;
use App\Repository\MonthlyCostCoverageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgressController extends AbstractController
{
    private FundingRepository $fundingRepository;
    private MonthlyCostCoverageRepository $monthlyCostCoverageRepository;

    public function __construct(
        FundingRepository $fundingRepository,
        MonthlyCostCoverageRepository $monthlyCostCoverageRepository
    ) {
        $this->fundingRepository = $fundingRepository;
        $this->monthlyCostCoverageRepository = $monthlyCostCoverageRepository;
    }

    /**
     * @Route("/api/funding/progress", name="funding_progress", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $progress = $this->fundingRepository->getCurrentProgressCostCoverage();

        $lastMonth = new \DateTimeImmutable('first day of last month');

        /** @var MonthlyCostCoverage $currentMonthCoverage */
        $currentMonthCoverage = $this->monthlyCostCoverageRepository->findOneBy(['month' => new \DateTimeImmutable('first day of')]);
        /** @var MonthlyCostCoverage $lastMonthCoverage */
        $lastMonthCoverage = $this->monthlyCostCoverageRepository->findOneBy(['month' => $lastMonth]);
        $defaultCostCoverage = null;

        // Current month
        if ($currentMonthCoverage === null) {
            // use default one
            /** @var MonthlyCostCoverage $defaultCostCoverage */
            $defaultCostCoverage = $this->monthlyCostCoverageRepository->findOneBy(['month' => new \DateTimeImmutable(MonthlyCostCoverage::DEFAULT_DATE)]);
            $target = $defaultCostCoverage->getTarget();
        } else {
            $target = $currentMonthCoverage->getTarget();
        }

        // Last month
        $lastMonthProgress = $this->fundingRepository->getProgressCostCoverageByMonth($lastMonth);
        if ($lastMonthCoverage === null) {
            // use default one
            if ($defaultCostCoverage === null) {
                /** @var MonthlyCostCoverage $defaultCostCoverage */
                $lastMonthCoverage = $this->monthlyCostCoverageRepository->findOneBy(['month' => new \DateTimeImmutable(MonthlyCostCoverage::DEFAULT_DATE)]);
            } else {
                $lastMonthCoverage = $defaultCostCoverage;
            }
        }
        if ($lastMonthCoverage->isPostpone()) {
            $target += max($lastMonthCoverage->getTarget() - $lastMonthProgress, 0);
        }

        return $this->json([
            'progress' => $progress,
            'target' => $target,
        ]);
    }
}
