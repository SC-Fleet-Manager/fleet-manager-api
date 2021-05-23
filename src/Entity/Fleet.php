<?php

namespace App\Entity;

use App\Domain\Event\UpdatedFleetEvent;
use App\Domain\Exception\NotFoundShipException;
use App\Domain\MyFleet\FleetShipImport;
use App\Domain\MyFleet\UserShipTemplate;
use App\Domain\Service\EntityIdGeneratorInterface;
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

    private array $events = [];

    public function __construct(UserId $userId, \DateTimeInterface $updatedAt)
    {
        $this->userId = $userId->getId();
        $this->ships = new ArrayCollection();
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function getAndClearEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
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

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function addShip(ShipId $id, string $model, ?string $imageUrl, int $quantity, \DateTimeInterface $updatedAt): void
    {
        $ship = new Ship($id, $this, $model, $imageUrl, $quantity);
        $this->ships[(string) $id] = $ship;
        $this->events[] = UpdatedFleetEvent::createFromFleet($this);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function addShipFromTemplate(UserShipTemplate $template, int $quantity, \DateTimeInterface $updatedAt, EntityIdGeneratorInterface $entityIdGenerator): void
    {
        $ship = Ship::createFromTemplate($entityIdGenerator->generateEntityId(ShipId::class), $this, $template, $quantity);
        $this->ships[(string) $ship->getId()] = $ship;
        $this->events[] = UpdatedFleetEvent::createFromFleet($this);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    /**
     * @return Ship[]
     */
    public function getShipsByModel(string $model): array
    {
        return array_filter($this->ships->toArray(), static function (Ship $ship) use ($model): bool {
            return $ship->looksModel($model);
        });
    }

    public function deleteShip(ShipId $shipId, \DateTimeInterface $updatedAt): void
    {
        $ship = $this->getShip($shipId);
        if ($ship === null) {
            return;
        }
        $this->ships->remove((string) $shipId);
        $this->events[] = UpdatedFleetEvent::createFromFleet($this);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function updateShip(ShipId $shipId, string $model, ?string $imageUrl, int $quantity, \DateTimeInterface $updatedAt): void
    {
        $ship = $this->getShip($shipId);
        if ($ship === null) {
            throw new NotFoundShipException($this->getUserId(), $shipId);
        }

        $ship->update($model, $imageUrl, $quantity);

        $this->events[] = UpdatedFleetEvent::createFromFleet($this);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    /**
     * @param FleetShipImport[] $importedShips
     */
    public function importShips(array $importedShips, bool $onlyMissing, \DateTimeInterface $updatedAt, EntityIdGeneratorInterface $entityIdGenerator): void
    {
        $addedShips = [];
        foreach ($importedShips as $importedShip) {
            $ships = $this->getShipsByModel($importedShip->model);
            if (empty($ships)) {
                $ship = new Ship($entityIdGenerator->generateEntityId(ShipId::class), $this, $importedShip->model, null, 1);
                $this->ships[(string) $ship->getId()] = $ship;
                $addedShips[(string) $ship->getId()] = $ship;
                continue;
            }
            $ship = array_shift($ships);
            if (!$onlyMissing || isset($addedShips[(string) $ship->getId()])) {
                $ship->update($importedShip->model, $ship->getImageUrl(), 1 + $ship->getQuantity());
            }
        }
        unset($addedShips);

        $this->events[] = UpdatedFleetEvent::createFromFleet($this);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    private function getShip(ShipId $shipId): ?Ship
    {
        return $this->ships[(string) $shipId] ?? null;
    }

    public function deleteAllShips(\DateTimeInterface $updatedAt): void
    {
        $this->ships->clear();
        $this->events[] = UpdatedFleetEvent::createFromFleet($this);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }
}
