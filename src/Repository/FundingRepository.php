<?php

namespace App\Repository;

use App\Entity\Funding;
use App\Entity\MonthlyCostCoverage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class FundingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Funding::class);
    }

    public function getCurrentProgressCostCoverage(): int
    {
        return $this->getProgressCostCoverageByMonth(new \DateTimeImmutable('first day of'));
    }

    public function getProgressCostCoverageByMonth(\DateTimeInterface $month): int
    {
        $beginDate = (new \DateTimeImmutable($month->format('Y-m-01 00:00')))->setTime(0, 0);
        $endDate = $beginDate->add(new \DateInterval('P1M'));

        $dql = <<<DQL
                SELECT SUM(f.netAmount) as totalNetAmount FROM App\Entity\Funding f
                WHERE f.createdAt >= :beginDate
                    AND f.createdAt < :endDate
                    AND f.paypalStatus IN ('COMPLETED', 'PARTIALLY_REFUNDED')
            DQL;
        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'beginDate' => $beginDate,
            'endDate' => $endDate,
        ]);
        $query->enableResultCache(3600);

        return $query->getSingleScalarResult() ?? 0;
    }
}
