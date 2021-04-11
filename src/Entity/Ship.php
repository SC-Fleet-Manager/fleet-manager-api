<?php

namespace App\Entity;

use App\Domain\ShipId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="ships")
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
     * @ORM\Column(name="model", type="string", length=32)
     */
    private string $model;

    /**
     * @ORM\Column(name="image_url", type="string", length=1023, nullable=true)
     */
    private ?string $imageUrl;

    /**
     * @ORM\Column(name="quantity", type="integer", options={"default":1})
     */
    private int $quantity;

    public function __construct(ShipId $id, Fleet $fleet, string $model, ?string $imageUrl = null, int $quantity = 1)
    {
        Assert::startsWith($imageUrl ?? 'http', 'http');
        Assert::lengthBetween($model, 2, 32);
        Assert::maxLength($imageUrl, 1023);
        $this->id = $id->getId();
        $this->fleet = $fleet;
        $this->model = $model;
        $this->imageUrl = $imageUrl;
        $this->quantity = max(1, $quantity);
    }

    public function getId(): ShipId
    {
        return new ShipId($this->id);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function update(string $model, ?string $imageUrl, int $quantity): void
    {
        $this->model = $model;
        $this->imageUrl = $imageUrl;
        $this->quantity = $quantity;
    }
}
