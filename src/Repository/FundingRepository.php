<?php

namespace App\Repository;

use App\Entity\Citizen;
use App\Entity\Funding;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;

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
        $stmt->enableResultCache(300);

        return $stmt->getSingleScalarResult() ?? 0;
    }

    public function getRankLadder(int $userTotalAmount, ?\DateTimeInterface $month = null): ?int
    {
        $monthSqlCondition = $month !== null ? '
            f.created_at >= :beginDate
            AND f.created_at < :endDate
            AND
        ' : '';

        // MySQL 8, use RANK() function
        $sql = <<<SQL
                SELECT IF(effectiveAmount.totalAmount = @_last_value, @curRank := @curRank, @curRank := @_sequence) as maxRank,
                        @_sequence := @_sequence+1, @_last_value := effectiveAmount.totalAmount
                FROM (
                    SELECT SUM(f.amount - f.refunded_amount) as totalAmount
                    FROM funding f
                    WHERE ${monthSqlCondition} f.paypal_status IN ('COMPLETED', 'PARTIALLY_REFUNDED', 'REFUNDED')
                    GROUP BY f.user_id
                    ORDER BY totalAmount DESC
                ) effectiveAmount,
                  (SELECT @curRank := 1, @_sequence := 1, @_last_value := 0) r
                WHERE totalAmount >= :userTotalAmount
                ORDER BY maxRank DESC
                LIMIT 1;
            SQL;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addScalarResult('maxRank', 'maxRank');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        $stmt->setParameter('userTotalAmount', $userTotalAmount);
        if ($month !== null) {
            $beginDate = (new \DateTimeImmutable($month->format('Y-m-01 00:00')));
            $endDate = $beginDate->add(new \DateInterval('P1M'));
            $stmt->setParameter('beginDate', $beginDate);
            $stmt->setParameter('endDate', $endDate);
        }
        $stmt->enableResultCache(300);

        return $stmt->getSingleResult()['maxRank'] ?? null;
    }



    public function getAlltimeLadder(int $limit = 20): array
    {
        return $this->getLadder($limit);
    }

    public function getMonthlyLadder(\DateTimeInterface $month, int $limit = 20): array
    {
        return $this->getLadder($limit, $month);
    }

    private function getLadder(int $limit, ?\DateTimeInterface $month = null): array
    {
        $fundingMetadata = $this->_em->getClassMetadata(Funding::class);
        $userMetadata = $this->_em->getClassMetadata(User::class);
        $citizenMetadata = $this->_em->getClassMetadata(Citizen::class);

        $monthSqlCondition = $month !== null ? '
            f.created_at >= :beginDate
            AND f.created_at < :endDate
            AND
        ' : '';

        $sql = <<<SQL
                SELECT u.id, u.nickname, u.username, c.actual_handle, SUM(f.amount - f.refunded_amount) as totalAmount
                FROM {$fundingMetadata->getTableName()} f
                INNER JOIN {$userMetadata->getTableName()} u ON u.id = f.user_id
                LEFT JOIN {$citizenMetadata->getTableName()} c ON c.id = u.citizen_id
                WHERE ${monthSqlCondition} f.paypal_status IN ('COMPLETED', 'PARTIALLY_REFUNDED', 'REFUNDED')
                GROUP BY f.user_id
                ORDER BY totalAmount DESC
                LIMIT ${limit}
            SQL;

        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addScalarResult('id', 'userId');
        $rsm->addScalarResult('username', 'username');
        $rsm->addScalarResult('nickname', 'nickname');
        $rsm->addScalarResult('actual_handle', 'actualHandle');
        $rsm->addScalarResult('totalAmount', 'totalAmount');

        $stmt = $this->_em->createNativeQuery($sql, $rsm);
        if ($month !== null) {
            $beginDate = (new \DateTimeImmutable($month->format('Y-m-01 00:00')));
            $endDate = $beginDate->add(new \DateInterval('P1M'));
            $stmt->setParameters([
                'beginDate' => $beginDate,
                'endDate' => $endDate,
            ]);
        }
        $stmt->enableResultCache(300);

        return $stmt->getResult();
    }
}
