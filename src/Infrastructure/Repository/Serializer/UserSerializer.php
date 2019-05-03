<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Serializer;

use App\Domain\User as DomainUser;
use App\Infrastructure\Entity\User;

class UserSerializer
{
    /**
     * @var CitizenSerializer
     */
    private $citizenSerializer;

    public function __construct(CitizenSerializer $citizenSerializer)
    {
        $this->citizenSerializer = $citizenSerializer;
    }

    public function toDomain(?User $userEntity): DomainUser
    {
        if ($userEntity === null) {
            return new DomainUser(null, ''); // null-object
        }
        $user = new DomainUser($userEntity->id, $userEntity->discordId);
        $user->token = $userEntity->token;
        $user->apiToken = $userEntity->apiToken;
        $user->username = $userEntity->username;
        $user->createdAt = clone $userEntity->createdAt;
        if ($userEntity->citizen !== null) {
            $user->citizen = $this->citizenSerializer->toDomain($userEntity->citizen);
        }

        return $user;
    }

    public function fromDomain(DomainUser $user): User
    {
        $e = new User();
        $e->id = clone $user->id;
        $e->username = $user->username;
        $e->discordId = $user->discordId;
        $e->token = $user->token;
        $e->apiToken = $user->apiToken;
        if ($user->citizen !== null) {
            $e->citizen = $this->citizenSerializer->fromDomain($user->citizen);
        }
        $e->createdAt = clone $user->createdAt;

        return $e;
    }
}
