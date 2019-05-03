<?php

namespace App\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     */
    public $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    public $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=false)
     */
    public $discordId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64, options={"fixed":true})
     */
    public $token;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64, options={"fixed":true})
     */
    public $apiToken;

    /**
     * @var Citizen
     *
     * @ORM\OneToOne(targetEntity="App\Infrastructure\Entity\Citizen", cascade={"persist"})
     */
    public $citizen;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable")
     */
    public $createdAt;
}
