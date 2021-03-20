<?php

namespace App\Repository;

use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\Funding;
use App\Entity\Organization;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\Persistence\ManagerRegistry;

class FundingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Funding::class);
    }

    public function deleteOldCreated(\DateTimeInterface $beforeDate): void
    {
        $dql = <<<DQL
            DELETE App\Entity\Funding f
            WHERE f.paypalStatus = 'CREATED' AND f.createdAt < :beforeDate
            DQL;

        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'beforeDate' => $beforeDate,
        ]);
        $query->execute();
    }

    public function getCurrentProgressCostCoverage(): int
    {
        return $this->getProgressCostCoverageByMonth(new \DateTimeImmutable('first day of'));
    }

    public function getProgressCostCoverageByMonth(\DateTimeInterface $month): int
    {
        $beginDate = (new \DateTimeImmutable($month->format('Y-m-01 00:00')));
        $endDate = $beginDate->add(new \DateInterval('P1M'));

        $dql = <<<DQL
                SELECT SUM(f.netAmount - f.refundedNetAmount) as totalNetAmount FROM App\Entity\Funding f
                WHERE f.createdAt >= :beginDate
                    AND f.createdAt < :endDate
                    AND f.paypalStatus IN ('COMPLETED', 'PARTIALLY_REFUNDED', 'REFUNDED')
            DQL;
        $query = $this->_em->createQuery($dql);
        $query->setParameters([
            'beginDate' => $beginDate,
            'endDate' => $endDate,
        ]);
        $query->enableResultCache(300);

        return $query->getSingleScalarResult() ?? 0;
    }

    public function getTotalAmountByUser(string $userId, ?\DateTimeInterface $month = null): int
    {
        $monthSqlCondition = $month !== null ? '
            f.created_at >= :beginDate
            AND f.created_at < :endDate
            AND
        ' : '';

        $sql = <<<SQL
                SELECT SUM(f.amount - f.refunded_amount) as totalAmount
                FROM funding f
                WHERE f.user_id = :userId AND ${monthSqlCondition} f.paypal_status IN ('COMPLETED', 'PARTIALLY_REFUNDED', 'REFUNDED')
            SQL;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addScalarResult('totalAmount', 'totalAmount');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameter('userId', $userId);
        if ($month !== null) {
            $beginDate = (new \DateTimeImmutable($month->format('Y-m-01 00:00')));
            $endDate = $beginDate->add(new \DateInterval('P1M'));
            $stmt->setParameter('beginDate', $beginDate);
            $stmt->setParameter('endDate', $endDate);
        }
        $stmt->enableResultCache(30);

        return $stmt->getSingleScalarResult() ?? 0;
    }
}
