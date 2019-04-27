<?php

namespace App\Infrastructure\Repository;

use App\Domain\ShipRepositoryInterface;
use App\Infrastructure\Entity\Ship;
use App\Infrastructure\Repository\Serializer\ShipSerializer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

class ShipRepository extends ServiceEntityRepository implements ShipRepositoryInterface
{
    private $shipSerializer;

    public function __construct(ManagerRegistry $registry, ShipSerializer $shipSerializer)
    {
        parent::__construct($registry, Ship::class);
        $this->shipSerializer = $shipSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function all(): iterable
    {
        $all = $this->findAll();
        $ships = [];
        foreach ($all as $ship) {
            $ships[] = $this->shipSerializer->toDomain($ship);
        }

        return $ships;
    }

    public function distinctNames(): iterable
    {
        $qb = $this->createQueryBuilder('ships')
            ->distinct()
            ->select('ships.name')
            ->orderBy('ships.name');

        return array_map(function (array $result): string {
            return $result['name'];
        }, $qb->getQuery()->getScalarResult());
    }
}
