<?php

namespace App\Controller\Funding;

use App\Entity\MonthlyCostCoverage;
use App\Repository\FundingRepository;
use App\Repository\MonthlyCostCoverageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProgressController extends AbstractController
{
    public function __construct(
        private FundingRepository $fundingRepository,
        private MonthlyCostCoverageRepository $monthlyCostCoverageRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route("/api/funding/progress", name: "funding_progress", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
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
            if ($defaultCostCoverage === null) {
                $defaultCostCoverage = $this->createDefaultCostCoverage();
            }
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
                if ($lastMonthCoverage === null) {
                    $lastMonthCoverage = $this->createDefaultCostCoverage();
                }
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

    private function createDefaultCostCoverage(): MonthlyCostCoverage
    {
        $entity = new MonthlyCostCoverage(Uuid::uuid4());
        $entity->setMonth(new \DateTimeImmutable(MonthlyCostCoverage::DEFAULT_DATE));
        $entity->setTarget(0);
        $entity->setPostpone(true);
        $this->entityManager->persist($entity);
        $this->entityManager->flush();

        return $entity;
    }
}
