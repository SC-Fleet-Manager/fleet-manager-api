<?php

namespace App\Entity;

use App\Domain\MemberId;
use App\Domain\OrganizationShipId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="organization_ships")
 */
class OrganizationShip
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid")
     */
    private Ulid $id;

    /**
     * @ORM\ManyToOne(targetEntity="OrganizationFleet", inversedBy="ships")
     * @ORM\JoinColumn(name="organization_fleet_id", referencedColumnName="orga_id", onDelete="CASCADE")
     */
    private OrganizationFleet $fleet;

    /**
     * @var Collection|OrganizationShipMember[]
     *
     * @ORM\OneToMany(targetEntity="OrganizationShipMember", mappedBy="ship", cascade="all", indexBy="memberId", orphanRemoval=true)
     */
    private Collection $owners;

    /**
     * @ORM\Column(name="model", type="string", length=60)
     */
    private string $model;

    /**
     * @ORM\Column(name="image_url", type="string", length=1023, nullable=true)
     */
    private ?string $imageUrl = null;

    /**
     * @ORM\Column(name="quantity", type="integer", options={"default":0})
     */
    private int $quantity = 0;

    public function __construct(
        OrganizationShipId $id,
        OrganizationFleet $fleet,
        string $model,
    ) {
        Assert::lengthBetween($model, 2, 60);
        $this->id = $id->getId();
        $this->fleet = $fleet;
        $this->model = $model;
        $this->owners = new ArrayCollection();
    }

    public function getId(): OrganizationShipId
    {
        return new OrganizationShipId($this->id);
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

    /**
     * @return OrganizationShipMember[]
     */
    public function getOwners(): array
    {
        return $this->owners->toArray();
    }

    public function updateOwner(MemberId $ownerId, ?string $imageUrl, int $quantity): void
    {
        Assert::startsWith($imageUrl ?? 'http', 'http');
        Assert::maxLength($imageUrl, 1023);

        $owner = $this->owners[(string) $ownerId] ??= new OrganizationShipMember($ownerId, $this, $quantity);
        $owner->updateQuantity($quantity);

        $this->imageUrl ??= $imageUrl;
        $this->recomputeQuantity();
    }

    public function deleteOwner(MemberId $ownerId): void
    {
        $this->owners->remove((string) $ownerId);
        $this->recomputeQuantity();
    }

    public function hasNoQuantity(): bool
    {
        return $this->quantity === 0;
    }

    public function looksModel(string $model): bool
    {
        $collator = new \Collator('en');
        $collator->setStrength(\Collator::PRIMARY); // â == A
        $collator->setAttribute(\Collator::ALTERNATE_HANDLING, \Collator::SHIFTED); // ignore punctuations

        return $collator->compare($this->model, $model) === 0;
    }

    private function recomputeQuantity(): void
    {
        $this->quantity = 0;
        foreach ($this->owners as $owner) {
            $this->quantity += $owner->getQuantity();
        }
    }
}
