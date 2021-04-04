<?php

namespace App\Infrastructure\Repository\Fleet;

use App\Application\Exception\AlreadyExistingFleetForUserException;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\UserId;
use App\Entity\Fleet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineFleetRepository extends ServiceEntityRepository implements FleetRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fleet::class);
    }

    public function getFleetByUser(UserId $userId): ?Fleet
    {
        return $this->_em->createQuery(<<<DQL
                SELECT fleet FROM App\Entity\Fleet fleet
                WHERE fleet.userId = :userId
            DQL
        )
            ->setParameter('userId', $userId->getId()->toRfc4122())
            ->getOneOrNullResult();
    }

    public function save(Fleet $fleet): void
    {
        $this->_em->persist($fleet);
        try {
            $this->_em->flush();
        } catch (UniqueConstraintViolationException $e) {
            throw new AlreadyExistingFleetForUserException($fleet->getUserId(), null, 0, $e);
        }
    }
}
