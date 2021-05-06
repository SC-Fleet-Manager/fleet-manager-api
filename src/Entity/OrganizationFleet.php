<?php

namespace App\Entity;

use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\OrganizationShipId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="organization_fleets")
 */
class OrganizationFleet
{
    use VersionnableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(name="orga_id", type="ulid")
     */
    private Ulid $orgaId;

    /**
     * @var Collection|OrganizationShip[]
     *
     * @ORM\OneToMany(targetEntity="OrganizationShip", mappedBy="fleet", cascade="all", fetch="EAGER", indexBy="id", orphanRemoval=true)
     */
    private Collection $ships;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz_immutable")
     */
    private \DateTimeImmutable $updatedAt;

    public function __construct(OrgaId $orgaId, \DateTimeInterface $updatedAt)
    {
        $this->orgaId = $orgaId->getId();
        $this->ships = new ArrayCollection();
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function getOrgaId(): OrgaId
    {
        return new OrgaId($this->orgaId);
    }

    /**
     * @return OrganizationShip[]
     */
    public function getShips(): array
    {
        return $this->ships->toArray();
    }

    public function getUpdatedAt(): \DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function getShipByModel(string $model): ?OrganizationShip
    {
        /** @var OrganizationShip $ship */
        foreach ($this->ships as $ship) {
            if ($ship->looksModel($model)) {
                return $ship;
            }
        }

        return null;
    }

    public function createOrUpdateShip(
        OrganizationShipId $id,
        MemberId $memberId,
        string $model,
        ?string $imageUrl,
        int $quantity,
        \DateTimeInterface $updatedAt
    ): void {
        $ship = $this->getShipByModel($model);
        if ($ship === null) {
            $this->addShip($id, $memberId, $model, $imageUrl, $quantity, $updatedAt);

            return;
        }
        $ship->updateOwner($memberId, $imageUrl, $quantity);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function clearShips(\DateTimeInterface $updatedAt): void
    {
        $this->ships->clear();
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function deleteShip(MemberId $memberId, string $model, \DateTimeInterface $updatedAt): void
    {
        $ship = $this->getShipByModel($model);
        if ($ship === null) {
            return;
        }
        $ship->deleteOwner($memberId);
        if ($ship->hasNoQuantity()) {
            $this->ships->remove((string) $ship->getId());
        }
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function deleteShipsOfMember(MemberId $memberId, \DateTimeInterface $updatedAt): void
    {
        foreach ($this->ships as $ship) {
            $ship->deleteOwner($memberId);
            if ($ship->hasNoQuantity()) {
                $this->ships->remove((string) $ship->getId());
            }
        }
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    private function addShip(OrganizationShipId $id, MemberId $ownerId, string $model, ?string $imageUrl, int $quantity, \DateTimeInterface $updatedAt): void
    {
        Assert::null($this->getShipByModel($model), sprintf('Cannot add ship with same model "%s".', $model));
        $ship = new OrganizationShip($id, $this, $model);
        $ship->updateOwner($ownerId, $imageUrl, $quantity);
        $this->ships[(string) $id] = $ship;
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }
}
