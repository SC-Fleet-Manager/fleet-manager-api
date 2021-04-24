<?php

namespace App\Entity;

use App\Application\Provider\MemberProfileProviderInterface;
use App\Domain\MemberId;
use App\Domain\MemberProfile;
use App\Domain\OrgaId;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection|Membership[]
     *
     * @ORM\OneToMany(targetEntity="Membership", mappedBy="organization", cascade="all", orphanRemoval=true)
     */
    private Collection $memberships;

    public function __construct(OrgaId $id, MemberId $founderId, string $name, string $sid, ?string $logoUrl, \DateTimeInterface $updatedAt)
    {
        $this->id = $id->getId();
        $this->founderId = $founderId->getId();
        $this->name = $name;
        $this->sid = $sid;
        $this->logoUrl = $logoUrl;
        $this->updatedAt = $updatedAt;
        $this->memberships = new ArrayCollection();
        $this->addMember($founderId, true, $updatedAt);
    }

    public function getId(): OrgaId
    {
        return new OrgaId($this->id);
    }

    public function getSid(): string
    {
        return $this->sid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function getFounderId(): MemberId
    {
        return new MemberId($this->founderId);
    }

    public function hasNoMembers(): bool
    {
        return $this->memberships->isEmpty();
    }

    /**
     * @return MemberProfile[]
     */
    public function getCandidates(MemberProfileProviderInterface $memberProfileProvider): array
    {
        $candidateIds = [];
        foreach ($this->memberships as $membership) {
            if (!$membership->hasJoined()) {
                $candidateIds[] = $membership->getMemberId();
            }
        }

        return $memberProfileProvider->getProfiles($candidateIds);
    }

    public function addMember(MemberId $memberId, bool $joined, \DateTimeInterface $updatedAt): void
    {
        $this->memberships->add(new Membership($this, $memberId, $joined));
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function unjoinMember(MemberId $memberId, \DateTimeInterface $updatedAt): void
    {
        if ($this->isFounder($memberId)) {
            $this->promoteNewFounder();
        }

        foreach ($this->memberships as $key => $membership) {
            if ($membership->getMemberId()->equals($memberId)) {
                $this->memberships->remove($key);
                break;
            }
        }
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    public function isMemberOf(MemberId $memberId): bool
    {
        return $this->getMembership($memberId) !== null;
    }

    public function hasJoined(MemberId $memberId): bool
    {
        $membership = $this->getMembership($memberId);
        if ($membership === null) {
            return false;
        }

        return $membership->hasJoined();
    }

    public function isFounder(MemberId $memberId): bool
    {
        return $this->founderId->equals($memberId->getId());
    }

    private function promoteNewFounder(): void
    {
        $oldFounder = $this->getFounderId();
        foreach ($this->memberships as $membership) {
            if (!$this->isFounder($membership->getMemberId()) && !$oldFounder->equals($membership->getMemberId())) {
                $this->founderId = clone $membership->getMemberId()->getId();
                break;
            }
        }
    }

    public function acceptCandidate(MemberId $candidateId, \DateTimeInterface $updatedAt): void
    {
        $membership = $this->getMembership($candidateId);
        if ($membership === null) {
            return;
        }
        $membership->accept();
        $this->updatedAt = \DateTimeImmutable::createFromInterface($updatedAt);
    }

    private function getMembership(MemberId $memberId): ?Membership
    {
        foreach ($this->memberships as $membership) {
            if ($membership->getMemberId()->equals($memberId)) {
                return $membership;
            }
        }

        return null;
    }
}
