<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShipChassisRepository")
 */
class ShipChassis
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\Column(type="integer", unique=true)
     */
    private int $rsiId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $name;

    public function __construct(?UuidInterface $id = null, int $rsiId = 0, string $name = '')
    {
        $this->id = $id;
        $this->rsiId = $rsiId;
        $this->name = $name;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getRsiId(): int
    {
        return $this->rsiId;
    }

    public function setRsiId(int $rsiId): self
    {
        $this->rsiId = $rsiId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
