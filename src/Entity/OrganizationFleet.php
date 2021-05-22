<?php

namespace App\Entity;

use App\Domain\Event\UpdatedShip;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\OrganizationShipId;
use App\Domain\Service\EntityIdGeneratorInterface;
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
     * @var Collection|OrganizationFleetMemberVersion[]
     *
     * @ORM\OneToMany(targetEntity="OrganizationFleetMemberVersion", mappedBy="fleet", cascade="all", fetch="EAGER", indexBy="memberId", orphanRemoval=true)
     */
    private Collection $memberVersions;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz_immutable")
     */
    private \DateTimeImmutable $updatedAt;

    public function __construct(OrgaId $orgaId, \DateTimeInterface $updatedAt)
    {
        $this->orgaId = $orgaId->getId();
        $this->ships = new ArrayCollection();
        $this->memberVersions = new ArrayCollection();
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
        MemberId $memberId,
        string $model,
        ?string $imageUrl,
        int $quantity,
        \DateTimeInterface $updatedAt,
        EntityIdGeneratorInterface $entityIdGenerator,
    ): void {
        $ship = $this->getShipByModel($model);
        if ($ship === null) {
            $this->addShip($entityIdGenerator->generateEntityId(OrganizationShipId::class), $memberId, $model, $imageUrl, $quantity, $updatedAt);

            return;
        }
        $ship->updateOwner($memberId, $imageUrl, $quantity);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function isNewMemberFleetVersion(MemberId $memberId, int $version): bool
    {
        $memberVersion = $this->memberVersions[(string) $memberId] ?? null;
        if ($memberVersion === null) {
            return true;
        }

        return $memberVersion->isNewMemberFleetVersion($version);
    }

    /**
     * @param UpdatedShip[] $shipsToUpdate
     */
    public function updateMemberFleet(MemberId $memberId, array $shipsToUpdate, int $version, \DateTimeInterface $updatedAt, EntityIdGeneratorInterface $entityIdGenerator): void
    {
        Assert::allIsInstanceOf($shipsToUpdate, UpdatedShip::class);

        // add missing ships
        foreach ($shipsToUpdate as $shipToUpdate) {
            $ship = $this->getShipByModel($shipToUpdate->model);
            if ($ship === null) {
                $this->addShip(
                    $entityIdGenerator->generateEntityId(OrganizationShipId::class),
                    $memberId,
                    $shipToUpdate->model,
                    $shipToUpdate->logoUrl,
                    $shipToUpdate->quantity,
                    $updatedAt,
                );
            }
        }

        // compute all this member ships
        foreach ($this->ships as $orgaShip) {
            /** @var UpdatedShip[] $shipsToUpdateInOrga */
            $shipsToUpdateInOrga = [];
            foreach ($shipsToUpdate as $shipToUpdate) {
                if ($orgaShip->looksModel($shipToUpdate->model)) {
                    $shipsToUpdateInOrga[] = $shipToUpdate;
                }
            }
            if (empty($shipsToUpdateInOrga)) {
                $this->deleteShipOfOwner($memberId, $orgaShip);
                continue;
            }
            $quantity = 0;
            $logoUrl = null;
            foreach ($shipsToUpdateInOrga as $shipToUpdateInOrga) {
                $quantity += $shipToUpdateInOrga->quantity;
                $logoUrl ??= $shipToUpdateInOrga->logoUrl;
            }
            $orgaShip->updateOwner($memberId, $logoUrl, $quantity);
        }

        $this->updateMemberVersion($memberId, $version);

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
        $this->deleteShipOfOwner($memberId, $ship);
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    private function deleteShipOfOwner(MemberId $ownerId, OrganizationShip $ship): void
    {
        $ship->deleteOwner($ownerId);
        if ($ship->hasNoQuantity()) {
            $this->ships->remove((string) $ship->getId());
        }
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

    private function updateMemberVersion(MemberId $memberId, int $version): void
    {
        if (!isset($this->memberVersions[(string) $memberId])) {
            $this->memberVersions[(string) $memberId] = new OrganizationFleetMemberVersion($memberId, $this, 1);
        }
        $this->memberVersions[(string) $memberId]->updateVersion($version);
    }
}
