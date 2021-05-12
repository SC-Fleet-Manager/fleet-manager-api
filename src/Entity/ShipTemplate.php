<?php

namespace App\Entity;

use App\Domain\ShipTemplateId;
use App\Domain\TemplateAuthorId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity
 * @ORM\Table(name="ship_templates")
 */
class ShipTemplate
{
    use VersionnableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid")
     */
    private Ulid $id;
    /**
     * @ORM\Column(name="author_id", type="ulid")
     */
    private Ulid $authorId;

    /**
     * @ORM\Column(name="model", type="string", length=60)
     */
    private string $model;

    /**
     * @ORM\Column(name="image_url", type="string", length=1023, nullable=true)
     */
    private ?string $pictureUrl;

    /**
     * @ORM\Embedded(class=ShipChassis::class, columnPrefix="chassis_")
     */
    private ShipChassis $chassis;

    /**
     * @ORM\Embedded(class=Manufacturer::class, columnPrefix="manufacturer_")
     */
    private Manufacturer $manufacturer;

    /**
     * @ORM\Embedded(class=ShipSize::class, columnPrefix="ship_size_")
     */
    private ShipSize $size;

    /**
     * @ORM\Embedded(class=ShipRole::class, columnPrefix="ship_role_")
     */
    private ShipRole $role;

    /**
     * @ORM\Embedded(class=CargoCapacity::class, columnPrefix="cargo_capacity_")
     */
    private CargoCapacity $cargoCapacity;

    /**
     * @ORM\Embedded(class=Crew::class, columnPrefix="crew_")
     */
    private Crew $crew;

    /**
     * @ORM\Embedded(class=Price::class, columnPrefix="price_")
     */
    private Price $price;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz_immutable")
     */
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        ShipTemplateId $id,
        TemplateAuthorId $authorId,
        string $model,
        ?string $pictureUrl,
        ShipChassis $chassis,
        Manufacturer $manufacturer,
        ShipSize $size,
        ShipRole $role,
        CargoCapacity $cargoCapacity,
        Crew $crew,
        Price $price,
        \DateTimeInterface $updatedAt,
    ) {
        $this->id = $id->getId();
        $this->authorId = $authorId->getId();
        $this->model = $model;
        $this->pictureUrl = $pictureUrl;
        $this->chassis = $chassis;
        $this->manufacturer = $manufacturer;
        $this->size = $size;
        $this->role = $role;
        $this->cargoCapacity = $cargoCapacity;
        $this->crew = $crew;
        $this->price = $price;
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function getId(): ShipTemplateId
    {
        return new ShipTemplateId($this->id);
    }

    public function getAuthorId(): TemplateAuthorId
    {
        return new TemplateAuthorId($this->authorId);
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getPictureUrl(): ?string
    {
        return $this->pictureUrl;
    }

    public function getChassis(): ShipChassis
    {
        return $this->chassis;
    }

    public function getManufacturer(): Manufacturer
    {
        return $this->manufacturer;
    }

    public function getSize(): ShipSize
    {
        return $this->size;
    }

    public function getRole(): ShipRole
    {
        return $this->role;
    }

    public function getCargoCapacity(): CargoCapacity
    {
        return $this->cargoCapacity;
    }

    public function getCrew(): Crew
    {
        return $this->crew;
    }

    public function getPrice(): Price
    {
        return $this->price;
    }
}
