<?php

namespace App\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 */
class Fleet
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     */
    public $id;

    /**
     * @var Citizen
     *
     * @ORM\ManyToOne(targetEntity="App\Infrastructure\Entity\Citizen", inversedBy="fleets")
     */
    public $owner;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime")
     */
    public $uploadDate;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    public $version;

    /**
     * @var iterable|Ship[]
     *
     * @ORM\OneToMany(targetEntity="App\Infrastructure\Entity\Ship", mappedBy="fleet", fetch="EAGER")
     */
    public $ships;

    public function __construct()
    {
        $this->ships = [];
    }
}
