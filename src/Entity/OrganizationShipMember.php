<?php

namespace App\Entity;

use App\Domain\MemberId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="organization_ship_members")
 */
class OrganizationShipMember
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="member_id", type="ulid")
     */
    private Ulid $memberId;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="OrganizationShip", inversedBy="owners")
     * @ORM\JoinColumn(name="organization_ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private OrganizationShip $ship;

    /**
     * @ORM\Column(name="quantity", type="integer")
     */
    private int $quantity;

    public function __construct(MemberId $memberId, OrganizationShip $ship, int $quantity)
    {
        $this->memberId = $memberId->getId();
        $this->ship = $ship;
        $this->updateQuantity($quantity);
    }

    public function getMemberId(): MemberId
    {
        return new MemberId($this->memberId);
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function updateQuantity(int $quantity): void
    {
        Assert::greaterThanEq($quantity, 1);
        $this->quantity = $quantity;
    }
}
