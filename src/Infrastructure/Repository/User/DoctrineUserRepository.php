<?php

namespace App\Infrastructure\Repository\User;

use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

class DoctrineUserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function countUsers(): int
    {
        return $this->_em
            ->createQuery('SELECT COUNT(u) FROM App\Entity\User u')
            ->getSingleScalarResult();
    }

    public function findByAuth0Username(string $username): ?User
    {
        return $this->findOneBy(['auth0Username' => $username]);
    }

    public function getById(UserId $userId): ?User
    {
        return $this->find($userId->getId());
    }

    public function save(User $user): void
    {
        $this->_em->persist($user);
        try {
            $this->_em->flush();
            $this->_em->clear();
        } catch (UniqueConstraintViolationException $e) {
            // alright, it's already persisted
        }
    }

    public function delete(User $user): void
    {
        $this->_em->remove($user);
        $this->_em->flush();
        $this->_em->clear();
    }
}
