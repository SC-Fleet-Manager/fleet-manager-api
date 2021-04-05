<?php

namespace App\Entity;

use App\Domain\ShipId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ships", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="fleetid_name_idx", columns={"fleet_id", "name"})
 * })
 */
class Ship
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid", unique=true)
     */
    private Ulid $id;

    /**
     * @ORM\ManyToOne(targetEntity="Fleet", inversedBy="ships")
     * @ORM\JoinColumn(name="fleet_id", referencedColumnName="user_id")
     */
    private Fleet $fleet;

    /**
     * @ORM\Column(name="name", type="string", length=32)
     */
    private string $name;

    /**
     * @ORM\Column(name="image_url", type="string", length=1023, nullable=true)
     */
    private ?string $imageUrl;

    /**
     * @ORM\Column(name="quantity", type="integer", options={"default":1})
     */
    private int $quantity;

    public function __construct(ShipId $id, Fleet $fleet, string $name, ?string $imageUrl = null, int $quantity = 1)
    {
        Assert::greaterThanEq($quantity, 1);
        Assert::startsWith($imageUrl ?? 'http', 'http');
        Assert::lengthBetween($name, 2, 32);
        Assert::maxLength($imageUrl, 1023);
        $this->id = $id->getId();
        $this->fleet = $fleet;
        $this->name = $name;
        $this->imageUrl = $imageUrl;
        $this->quantity = $quantity;
    }

    public function getId(): ShipId
    {
        return new ShipId($this->id);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function incrementQuantity(int $step): void
    {
        $this->quantity += $step;
        $this->quantity = max(1, min(1_000_000_000, $this->quantity));
    }
}
