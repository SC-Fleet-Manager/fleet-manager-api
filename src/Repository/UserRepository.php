<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
