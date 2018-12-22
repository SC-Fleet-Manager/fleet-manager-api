<?php

namespace App\Domain;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /**
     * @var UuidInterface
     */
    public $id;

    /**
     * @var string
     */
    public $username;

    /**
     * @var \DateTimeInterface
     */
    public $createdAt;

    /**
     * @var Citizen
     */
    public $citizen;

    public function __construct(?UuidInterface $id, string $username)
    {
        $this->id = $id;
        $this->username = $username;
    }

    public function getRoles()
    {
        return ['ROLE_USER'];
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function eraseCredentials()
    {
    }
}
