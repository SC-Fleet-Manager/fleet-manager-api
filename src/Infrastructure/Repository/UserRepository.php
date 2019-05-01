<?php

namespace App\Infrastructure\Repository;

use App\Domain\User as DomainUser;
use App\Domain\Citizen as DomainCitizen;
use App\Domain\Fleet as DomainFleet;
use App\Domain\HandleSC;
use App\Domain\SpectrumIdentification;
use App\Domain\UserRepositoryInterface;
use App\Infrastructure\Entity\Citizen;
use App\Infrastructure\Entity\User;
use App\Infrastructure\Repository\Serializer\CitizenSerializer;
use App\Infrastructure\Repository\Serializer\FleetSerializer;
use App\Infrastructure\Repository\Serializer\UserSerializer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    /**
     * @var UserSerializer
     */
    private $userSerializer;

    public function __construct(ManagerRegistry $registry, UserSerializer $userSerializer)
    {
        parent::__construct($registry, User::class);
        $this->userSerializer = $userSerializer;
    }

    /**
     * {@inheritdoc}
     */
    public function getByUsername(string $username): ?DomainUser
    {
        /** @var User $userEntity */
        $userEntity = $this->createQueryBuilder('u')
            ->addSelect('c')
            ->leftJoin('u.citizen', 'c')
            ->where('u.username = :username')
            ->setParameter('username', $username)
            ->getQuery()
            ->getOneOrNullResult();
        if ($userEntity === null) {
            return null;
        }

        $user = $this->userSerializer->toDomain($userEntity);

        return $user;
    }

    public function getByDiscordId(string $id): ?DomainUser
    {
        /** @var User $userEntity */
        $userEntity = $this->createQueryBuilder('u')
            ->addSelect('c')
            ->leftJoin('u.citizen', 'c')
            ->where('u.discordId = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
        if ($userEntity === null) {
            return null;
        }

        $user = $this->userSerializer->toDomain($userEntity);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function create(DomainUser $user): void
    {
        $entity = $this->userSerializer->fromDomain($user);

        $em = $this->getEntityManager();
        $em->clear();
        $em->persist($entity);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function update(DomainUser $user): void
    {
        $entity = $this->userSerializer->fromDomain($user);

        $em = $this->getEntityManager();
        $em->clear();
        $em->merge($entity);
        $em->flush();
    }
}
