<?php

namespace App\Infrastructure\Repository\Fleet;

use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Exception\AlreadyExistingFleetForUserException;
use App\Domain\Exception\ConflictVersionException;
use App\Domain\UserId;
use App\Entity\Fleet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class DoctrineFleetRepository extends ServiceEntityRepository implements FleetRepositoryInterface, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        ManagerRegistry $registry,
        private MessageBusInterface $eventBus
    ) {
        parent::__construct($registry, Fleet::class);
    }

    public function getFleetByUser(UserId $userId): ?Fleet
    {
        return $this->_em->createQuery(<<<DQL
                SELECT fleet, ship FROM App\Entity\Fleet fleet
                LEFT JOIN fleet.ships ship
                WHERE fleet.userId = :userId
            DQL
        )
            ->setParameter('userId', $userId)
            ->getOneOrNullResult();
    }

    public function save(Fleet $fleet): void
    {
        $this->_em->persist($fleet);
        $this->_em->beginTransaction();
        try {
            $this->_em->flush();
            foreach ($fleet->getAndClearEvents() as $event) {
                $this->eventBus->dispatch($event);
            }
            $this->_em->commit();
            $this->_em->clear();
        } catch (OptimisticLockException $e) {
            $this->_em->rollback();
            $this->logger->warning('conflict version on save ship.', ['exception' => $e]);
            throw new ConflictVersionException($fleet, 'Unable to save your fleet. Please, try again.', context: ['userId' => $fleet->getUserId()], previous: $e);
        } catch (UniqueConstraintViolationException $e) {
            $this->_em->rollback();
            $this->logger->warning('already existing fleet.', ['fleetId' => $fleet->getUserId(), 'exception' => $e]);
            throw new AlreadyExistingFleetForUserException($fleet->getUserId(), previous: $e);
        } catch (\Throwable $e) {
            $this->_em->rollback();
            throw $e;
        }
    }

    public function delete(Fleet $fleet): void
    {
        $this->_em->remove($fleet);
        $this->_em->flush();
        $this->_em->clear();
    }
}
