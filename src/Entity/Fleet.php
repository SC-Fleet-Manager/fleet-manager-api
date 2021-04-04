<?php

namespace App\Entity;

use App\Domain\FleetId;
use App\Domain\ShipId;
use App\Domain\UserId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity
 * @ORM\Table(name="fleets")
 */
class Fleet
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid", unique=true)
     */
    private Ulid $id;

    /**
     * @ORM\Column(name="user_id", type="ulid", unique=true)
     */
    private Ulid $userId;

    /**
     * @var Collection<Ship>
     *
     * @ORM\OneToMany(targetEntity="Ship", mappedBy="fleet", cascade="persist")
     */
    private Collection $ships;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz_immutable")
     */
    private \DateTimeImmutable $updatedAt;

    public function __construct(FleetId $id, UserId $userId, \DateTimeInterface $updatedAt)
    {
        $this->id = $id->getId();
        $this->userId = $userId->getId();
        $this->ships = new ArrayCollection();
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function getId(): FleetId
    {
        return new FleetId($this->id);
    }

    public function getUserId(): UserId
    {
        return new UserId($this->userId);
    }

    /**
     * @return Collection|Ship[]
     */
    public function getShips(): Collection
    {
        return $this->ships;
    }

    public function addShip(ShipId $id, string $name, ?string $imageUrl, int $quantity, \DateTimeInterface $updatedAt): void
    {
        $this->ships[] = new Ship($id, $this, $name, $imageUrl, $quantity);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }
}
