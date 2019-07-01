<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ORM\Table(indexes={
 *     @ORM\Index(name="type_idx", columns={"type"})
 * })
 */
class OrganizationChange
{
    public const TYPE_UNKNOWN = 'unknown';
    /** When someone has changed the privacy policy of the orga with the old+new values */
    public const TYPE_UPDATE_PRIVACY_POLICY = 'update_privacy_policy';
    /** When someone has updated his fleet and there is a diff */
    public const TYPE_UPLOAD_FLEET = 'upload_fleet';
    /** When someone has joined the orga. i.e. when the refreshed citizen has added this orga. */
    public const TYPE_JOIN_ORGA = 'join_orga';
    /** When someone has leaved the orga */
    public const TYPE_LEAVE_ORGA = 'leave_orga';

    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     *
     * @Groups({"orga_fleet_admin"})
     */
    private $id;

    /**
     * @var Organization
     *
     * @ORM\ManyToOne(targetEntity="Organization", inversedBy="changes")
     */
    private $organization;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=31)
     *
     * @Groups({"orga_fleet_admin"})
     */
    private $type;

    /**
     * - update_privacy_policy : the old and new values of the of the policy
     * - upload_fleet : the count (positive or negative) of each ships added/removed
     * - join_orga : empty
     * - leave_orga : empty.
     *
     * @var array
     *
     * @ORM\Column(type="json")
     *
     * @Groups({"orga_fleet_admin"})
     */
    private $payload;

    /**
     * @var Citizen
     *
     * @ORM\ManyToOne(targetEntity="Citizen")
     *
     * @Groups({"orga_fleet_admin"})
     */
    private $author;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable")
     *
     * @Groups({"orga_fleet_admin"})
     */
    private $createdAt;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->payload = [];
        $this->type = self::TYPE_UNKNOWN;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

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

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function setPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function getAuthor(): ?Citizen
    {
        return $this->author;
    }

    public function setAuthor(?Citizen $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
