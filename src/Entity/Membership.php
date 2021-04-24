<?php

namespace App\Entity;

use App\Domain\MemberId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity
 * @ORM\Table(name="memberships", indexes={})
 */
class Membership
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="member_id", type="ulid")
     */
    private Ulid $memberId;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="memberships")
     * @ORM\JoinColumn(name="organization_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private Organization $organization;

    /**
     * @ORM\Column(name="joined", type="boolean", options={"default":false})
     */
    private bool $joined;

    public function __construct(Organization $organization, MemberId $memberId, bool $joined)
    {
        $this->memberId = $memberId->getId();
        $this->organization = $organization;
        $this->joined = $joined;
    }

    public function getMemberId(): MemberId
    {
        return new MemberId($this->memberId);
    }

    public function hasJoined(): bool
    {
        return $this->joined;
    }
}
