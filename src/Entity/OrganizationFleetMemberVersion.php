<?php

namespace App\Entity;

use App\Domain\MemberId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="organization_fleet_member_versions")
 */
class OrganizationFleetMemberVersion
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="member_id", type="ulid")
     */
    private Ulid $memberId;

    /**
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="OrganizationFleet", inversedBy="memberVersions")
     * @ORM\JoinColumn(name="organization_fleet_id", referencedColumnName="orga_id", onDelete="CASCADE")
     */
    private OrganizationFleet $fleet;

    /**
     * @ORM\Column(name="version", type="integer", options={"default":1})
     */
    private int $version;

    public function __construct(MemberId $memberId, OrganizationFleet $fleet, int $version)
    {
        Assert::greaterThanEq($version, 1);
        $this->memberId = $memberId->getId();
        $this->fleet = $fleet;
        $this->version = $version;
    }

    public function getMemberId(): MemberId
    {
        return new MemberId($this->memberId);
    }

    public function isNewMemberFleetVersion(int $newVersion): bool
    {
        return $newVersion > $this->version;
    }

    public function updateVersion(int $newVersion): void
    {
        Assert::greaterThanEq($newVersion, 1);
        $this->version = $newVersion;
    }
}
