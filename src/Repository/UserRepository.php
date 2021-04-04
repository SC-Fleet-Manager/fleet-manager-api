<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository
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

    public function save(User $user): void
    {
        $this->_em->persist($user);
        try {
            $this->_em->flush();
        } catch (UniqueConstraintViolationException $e) {
            // alright, it's already persisted
        }
    }
}
