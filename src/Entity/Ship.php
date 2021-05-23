<?php

namespace App\Entity;

use App\Domain\MyFleet\UserShipTemplate;
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
     * @ORM\Column(name="template_id", type="ulid", nullable=true)
     */
    private ?Ulid $templateId = null;

    /**
     * @ORM\ManyToOne(targetEntity="Fleet", inversedBy="ships")
     * @ORM\JoinColumn(name="fleet_id", referencedColumnName="user_id")
     */
    private Fleet $fleet;

    /**
     * @ORM\Column(name="model", type="string", length=60)
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
        Assert::lengthBetween($model, 2, 60);
        Assert::maxLength($imageUrl, 1023);
        $this->id = $id->getId();
        $this->fleet = $fleet;
        $this->model = $model;
        $this->imageUrl = $imageUrl;
        $this->quantity = max(1, $quantity);
    }

    public static function createFromTemplate(ShipId $id, Fleet $fleet, UserShipTemplate $template, int $quantity = 1): self
    {
        $ship = new self(
            $id,
            $fleet,
            $template->getModel(),
            $template->getPictureUrl(),
            $quantity,
        );
        $ship->templateId = $template->getId()->getId();

        return $ship;
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
        Assert::lengthBetween($model, 2, 60);
        Assert::maxLength($imageUrl, 1023);
        $this->model = $model;
        $this->imageUrl = $imageUrl;
        $this->quantity = $quantity;
    }

    public function looksModel(string $model): bool
    {
        $collator = new \Collator('en');
        $collator->setStrength(\Collator::PRIMARY); // â == A
        $collator->setAttribute(\Collator::ALTERNATE_HANDLING, \Collator::SHIFTED); // ignore punctuations

        return $collator->compare($this->model, $model) === 0;
    }
}
