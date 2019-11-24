<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShipRepository")
 * @ORM\Table(indexes={@ORM\Index(name="name_idx", columns={"name"})})
 */
class Ship
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private $rawData;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private $manufacturer;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private $pledgeDate;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"my-fleet"})
     */
    private $cost;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":false})
     * @Groups({"my-fleet", "public-fleet"})
     */
    private $insured;

    /**
     * @var Fleet
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Fleet", inversedBy="ships")
     */
    private $fleet;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->insured = false;
        $this->rawData = [];
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getOwner(): ?Citizen
    {
        return $this->fleet->getOwner();
    }

    public function getRawData(): array
    {
        return $this->rawData;
    }

    public function setRawData(array $rawData): self
    {
        $this->rawData = $rawData;

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

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(?string $manufacturer): self
    {
        $this->manufacturer = $manufacturer;

        return $this;
    }

    public function getPledgeDate(): ?\DateTimeImmutable
    {
        return $this->pledgeDate;
    }

    public function setPledgeDate(?\DateTimeImmutable $pledgeDate): self
    {
        $this->pledgeDate = $pledgeDate;

        return $this;
    }

    public function getCost(): float
    {
        return $this->cost;
    }

    public function setCost(float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    public function isInsured(): bool
    {
        return $this->insured;
    }

    public function setInsured(bool $insured): self
    {
        $this->insured = $insured;

        return $this;
    }

    public function getFleet(): ?Fleet
    {
        return $this->fleet;
    }

    public function setFleet(Fleet $fleet): self
    {
        $this->fleet = $fleet;
        $fleet->addShip($this);

        return $this;
    }

    public function equals(self $other): bool
    {
        return mb_strtolower($this->name) === mb_strtolower($other->name)
            && mb_strtolower($this->manufacturer) === mb_strtolower($other->manufacturer)
            && $this->insured === $other->insured
            && $this->cost === $other->cost
            && $this->pledgeDate->format('Ymd') === $other->pledgeDate->format('Ymd');
    }
}
