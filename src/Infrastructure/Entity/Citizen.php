<?php

namespace App\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 */
class Citizen
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
    public $number;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    public $actualHandle;

    /**
     * @var iterable|string[]
     *
     * @ORM\Column(type="json")
     */
    public $organisations;

    /**
     * @var iterable|Fleet[]
     *
     * @ORM\OneToMany(targetEntity="App\Infrastructure\Entity\Fleet", mappedBy="owner")
     */
    public $fleets;

    public function __construct()
    {
        $this->organisations = [];
        $this->fleets = [];
    }

    public static function fromCitizen(\App\Domain\Citizen $citizen): self
    {
        $e = new self();
        $e->id = clone $citizen->id;
        $e->number = (string) $citizen->number;
        $e->actualHandle = (string) $citizen->actualHandle;
        foreach ($citizen->organisations as $orga) {
            $e->organisations[] = (string) $orga;
        }

        return $e;
    }
}
