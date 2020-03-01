<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShipRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="name_idx", columns={"name"})
 * })
 */
class Ship
{
    public const INSURANCE_TYPE_LTI = 'lti';
    public const INSURANCE_TYPE_IAE = 'iae';
    public const INSURANCE_TYPE_MONTHLY = 'monthly';
    public const INSURANCE_TYPES = [
        self::INSURANCE_TYPE_LTI,
        self::INSURANCE_TYPE_IAE,
        self::INSURANCE_TYPE_MONTHLY,
    ];

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private ?UuidInterface $id = null;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    private array $rawData = [];

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private ?string $manufacturer = null;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private ?\DateTimeImmutable $pledgeDate = null;

    /**
     * @ORM\Column(type="float", nullable=true)
     * @Groups({"my-fleet"})
     */
    private ?float $cost = null;

    /**
     * @deprecated since 1.4.1j
     *
     * Lifetime insured.
     *
     * @ORM\Column(type="boolean", options={"default":false})
     * @Groups({"my-fleet", "public-fleet"})
     */
    private bool $insured = false;

    /**
     * @see self::INSURANCE_TYPES
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private ?string $insuranceType = null;

    /**
     * In months.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"my-fleet", "public-fleet"})
     */
    private ?int $insuranceDuration = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Fleet", inversedBy="ships")
     */
    private ?Fleet $fleet = null;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
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

    public function getCost(): ?float
    {
        return $this->cost;
    }

    public function setCost(?float $cost): self
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * @deprecated since 1.4.1j
     */
    public function isInsured(): bool
    {
        return $this->insured;
    }

    /**
     * @deprecated since 1.4.1j
     */
    public function setInsured(bool $insured): self
    {
        $this->insured = $insured;

        return $this;
    }

    public function getInsuranceType(): ?string
    {
        return $this->insuranceType;
    }

    public function setInsuranceType(?string $insuranceType): self
    {
        if ($insuranceType !== null && !in_array($insuranceType, self::INSURANCE_TYPES, true)) {
            return $this;
        }
        $this->insuranceType = $insuranceType;

        return $this;
    }

    public function getInsuranceDuration(): ?int
    {
        return $this->insuranceDuration;
    }

    public function setInsuranceDuration(?int $insuranceDuration): self
    {
        $this->insuranceDuration = $insuranceDuration;

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
            && $this->insuranceType === $other->insuranceType
            && $this->insuranceDuration === $other->insuranceDuration
            && $this->cost === $other->cost
            && $this->pledgeDate->format('Ymd') === $other->pledgeDate->format('Ymd');
    }
}
