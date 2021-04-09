<?php

namespace App\Entity;

use App\Application\Common\Clock;
use App\Domain\Exception\NotFoundShipException;
use App\Domain\ShipId;
use App\Domain\UserId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="fleets")
 */
class Fleet
{
    use VersionnableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(name="user_id", type="ulid")
     */
    private Ulid $userId;

    /**
     * @var Collection|Ship[]
     *
     * @ORM\OneToMany(targetEntity="Ship", mappedBy="fleet", cascade="all", fetch="EAGER", indexBy="id", orphanRemoval=true)
     */
    private Collection $ships;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz_immutable")
     */
    private \DateTimeImmutable $updatedAt;

    public function __construct(UserId $userId, \DateTimeInterface $updatedAt)
    {
        $this->userId = $userId->getId();
        $this->ships = new ArrayCollection();
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function getUserId(): UserId
    {
        return new UserId($this->userId);
    }

    /**
     * @return Ship[]
     */
    public function getShips(): array
    {
        return $this->ships->toArray();
    }

    public function addShip(ShipId $id, string $name, ?string $imageUrl, int $quantity, \DateTimeInterface $updatedAt): void
    {
        Assert::null($this->getShipByName($name), sprintf('Cannot add ship with same name "%s".', $name));
        $this->ships[(string) $id] = new Ship($id, $this, $name, $imageUrl, max(1, $quantity));
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getShipByName(string $name): ?Ship
    {
        $collator = new \Collator('en');
        $collator->setStrength(\Collator::PRIMARY); // â == A
        $collator->setAttribute(\Collator::ALTERNATE_HANDLING, \Collator::SHIFTED); // ignore punctuations
        foreach ($this->ships as $ship) {
            if ($collator->compare($ship->getName(), $name) === 0) {
                return $ship;
            }
        }

        return null;
    }

    /**
     * @return int the new quantity of the ship
     */
    public function incrementShipQuantity(ShipId $shipId, int $step, Clock $clock): int
    {
        $ship = $this->getShip($shipId);
        if ($ship === null) {
            throw new NotFoundShipException($this->getUserId(), $shipId);
        }

        $ship->incrementQuantity($step);
        $this->updatedAt = $clock->now();

        return $ship->getQuantity();
    }

    private function getShip(ShipId $shipId): ?Ship
    {
        return $this->ships[(string) $shipId] ?? null;
    }

    public function deleteShip(ShipId $shipId, Clock $clock): void
    {
        $this->ships->remove((string) $shipId);
        $this->updatedAt = $clock->now();
    }
}
