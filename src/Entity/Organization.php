<?php

namespace App\Entity;

use App\Domain\OrgaId;
use App\Domain\UserId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity
 * @ORM\Table(name="organizations", indexes={
*    @ORM\Index(name="founder_idx", columns={"founder_id"})
 * })
 */
class Organization
{
    use VersionnableTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid")
     */
    private Ulid $id;

    /**
     * @ORM\Column(name="founder_id", type="ulid")
     */
    private Ulid $founderId;

    /**
     * @ORM\Column(name="name", type="string", length=32)
     */
    private string $name;

    /**
     * @ORM\Column(name="sid", type="string", length=15, unique=true)
     */
    private string $sid;

    /**
     * @ORM\Column(name="logo_url", type="string", length=1023, nullable=true)
     */
    private ?string $logoUrl;

    /**
     * @ORM\Column(name="updated_at", type="datetimetz_immutable")
     */
    private \DateTimeInterface $updatedAt;

    public function __construct(OrgaId $id, UserId $founderId, string $name, string $sid, ?string $logoUrl, \DateTimeInterface $updatedAt)
    {
        $this->id = $id->getId();
        $this->founderId = $founderId->getId();
        $this->name = $name;
        $this->sid = $sid;
        $this->logoUrl = $logoUrl;
        $this->updatedAt = $updatedAt;
    }

    public function getId(): OrgaId
    {
        return new OrgaId($this->id);
    }

    public function getSid(): string
    {
        return $this->sid;
    }

    public function getFounderId(): UserId
    {
        return new UserId($this->founderId);
    }
}
