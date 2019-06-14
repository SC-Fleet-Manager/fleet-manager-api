<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CitizenOrganizationRepository")
 */
class CitizenOrganization
{
    public const VISIBILITY_PRIVATE = 'private';
    public const VISIBILITY_ADMIN = 'admin';
    public const VISIBILITY_ORGA = 'orga';
    public const VISIBILITIES = [
        self::VISIBILITY_PRIVATE,
        self::VISIBILITY_ADMIN,
        self::VISIBILITY_ORGA,
    ];

    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"profile", "orga_fleet"})
     */
    private $id;

    /**
     * @var Citizen
     *
     * @ORM\ManyToOne(targetEntity="Citizen", inversedBy="organizations")
     */
    private $citizen;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=31)
     */
    private $organizationSid;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Organization", fetch="EAGER")
     * @Groups({"profile", "orga_fleet"})
     */
    private $organization;

    /**
     * orga rank from 0 to 5 (6 values).
     *
     * @var int
     *
     * @ORM\Column(type="smallint", options={"defaults":0})
     * @Groups({"profile", "orga_fleet"})
     */
    private $rank;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=31, nullable=true)
     * @Groups({"profile", "orga_fleet"})
     */
    private $rankName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, options={"default":"orga"})
     * @Groups({"profile"})
     */
    private $visibility;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->rank = 0;
        $this->visibility = self::VISIBILITY_ORGA;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getCitizen(): ?Citizen
    {
        return $this->citizen;
    }

    public function setCitizen(?Citizen $citizen): self
    {
        $this->citizen = $citizen;

        return $this;
    }

    public function getOrganizationSid(): ?string
    {
        return $this->organizationSid;
    }

    public function setOrganizationSid(?string $sid): self
    {
        $this->organizationSid = $sid;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getRank(): int
    {
        return $this->rank;
    }

    public function setRank(int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function getRankName(): ?string
    {
        return $this->rankName;
    }

    public function setRankName(?string $rankName): self
    {
        $this->rankName = $rankName;

        return $this;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function setVisibility(string $visibility): self
    {
        if (in_array($visibility, self::VISIBILITIES, true)) {
            $this->visibility = $visibility;
        }

        return $this;
    }
}
