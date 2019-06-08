<?php

namespace App\Entity;

use App\Domain\CitizenInfos;
use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CitizenRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="actualhandle_idx", columns={"actual_handle"})
 * })
 */
class Citizen
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"profile", "orga_fleet"})
     */
    private $id;

    /**
     * @var CitizenNumber
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"profile"})
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"profile", "orga_fleet"})
     */
    private $nickname;

    /**
     * @var HandleSC
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"profile", "orga_fleet"})
     */
    private $actualHandle;

    /**
     * @var iterable|Fleet[]
     *
     * @ORM\OneToMany(targetEntity="Fleet", mappedBy="owner")
     */
    private $fleets;

    /**
     * @var Fleet
     *
     * @ORM\OneToOne(targetEntity="Fleet")
     */
    private $lastFleet;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"profile"})
     */
    private $bio;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    private $lastRefresh;

    /**
     * @var iterable|CitizenOrganization[]
     *
     * @ORM\OneToMany(targetEntity="CitizenOrganization", mappedBy="citizen", fetch="EAGER", cascade={"all"}, orphanRemoval=true)
     * @Groups({"profile", "orga_fleet"})
     */
    private $organizations;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", options={"defaults":0})
     * @Groups({"profile", "orga_fleet"})
     */
    private $countRedactedOrganizations;

    /**
     * @var CitizenOrganization
     *
     * @ORM\OneToOne(targetEntity="CitizenOrganization", fetch="EAGER", cascade={"all"})
     * @Groups({"profile", "orga_fleet"})
     */
    private $mainOrga;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"defaults":false})
     * @Groups({"profile", "orga_fleet"})
     */
    private $redactedMainOrga;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->fleets = new ArrayCollection();
        $this->organizations = new ArrayCollection();
        $this->countRedactedOrganizations = 0;
        $this->redactedMainOrga = false;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getNumber(): ?CitizenNumber
    {
        if (!$this->number instanceof CitizenNumber) {
            $this->number = new CitizenNumber($this->number);
        }

        return $this->number;
    }

    public function setNumber(?CitizenNumber $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(?string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getActualHandle(): HandleSC
    {
        if (!$this->actualHandle instanceof HandleSC) {
            $this->actualHandle = new HandleSC($this->actualHandle);
        }

        return $this->actualHandle;
    }

    public function setActualHandle(?HandleSC $actualHandle): self
    {
        $this->actualHandle = $actualHandle;

        return $this;
    }

    public function getMainOrga(): ?CitizenOrganization
    {
        return $this->mainOrga;
    }

    public function setMainOrga(?CitizenOrganization $mainOrga): self
    {
        $this->mainOrga = $mainOrga;

        return $this;
    }

    /**
     * @return iterable|Fleet[]
     */
    public function getFleets(): iterable
    {
        return $this->fleets;
    }

    public function addFleet(Fleet $fleet): self
    {
        if ($fleet->getOwner() !== $this) {
            $fleet->setOwner($this);
        }
        if (!$this->fleets->contains($fleet)) {
            $this->fleets->add($fleet);
        }

        return $this;
    }

    public function removeFleet(Fleet $fleet): self
    {
        if ($fleet->getOwner() !== null) {
            $fleet->setOwner(null);
        }
        $this->fleets->removeElement($fleet);

        return $this;
    }

    public function getLastFleet(): ?Fleet
    {
        return $this->lastFleet;
    }

    public function setLastFleet(?Fleet $lastFleet): self
    {
        $this->lastFleet = $lastFleet;

        return $this;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    /**
     * @return iterable|CitizenOrganization[]
     */
    public function getOrganizations(): iterable
    {
        return $this->organizations;
    }

    /**
     * @return string[]
     */
    public function getOrganizationSids(): array
    {
        return $this->organizations->map(static function (CitizenOrganization $citizenOrganization): string {
            return $citizenOrganization->getOrganizationSid();
        })->toArray();
    }

    public function hasOrganization(string $sid): bool
    {
        foreach ($this->organizations as $orga) {
            if ($orga->getOrganization()->getOrganizationSid() === $sid) {
                return true;
            }
        }

        return false;
    }

    public function clearOrganizations(): self
    {
        $this->organizations->clear();

        return $this;
    }

    public function addOrganization(CitizenOrganization $orga): self
    {
        if ($orga->getCitizen() !== $this) {
            $orga->setCitizen($this);
        }
        $this->organizations->add($orga);

        return $this;
    }

    public function removeOrganization(CitizenOrganization $orga): self
    {
        if ($orga->getCitizen() !== null) {
            $orga->setCitizen(null);
        }
        $this->organizations->removeElement($orga);

        return $this;
    }

    public function getCountRedactedOrganizations(): int
    {
        return $this->countRedactedOrganizations;
    }

    public function setCountRedactedOrganizations(int $countRedactedOrganizations): self
    {
        $this->countRedactedOrganizations = $countRedactedOrganizations;

        return $this;
    }

    public function isRedactedMainOrga(): bool
    {
        return $this->redactedMainOrga;
    }

    public function setRedactedMainOrga(bool $redactedMainOrga): self
    {
        $this->redactedMainOrga = $redactedMainOrga;

        return $this;
    }

    public function getLastRefresh(): ?\DateTimeInterface
    {
        return $this->lastRefresh;
    }

    public function setLastRefresh(?\DateTimeInterface $lastRefresh): self
    {
        $this->lastRefresh = $lastRefresh;

        return $this;
    }

    public function canBeRefreshed(): bool
    {
        return $this->lastRefresh === null || $this->lastRefresh <= new \DateTimeImmutable('-30 minutes');
    }

    public function getTimeLeftBeforeRefreshing(): ?\DateInterval
    {
        if ($this->lastRefresh === null) {
            return null;
        }

        return $this->lastRefresh->diff(new \DateTimeImmutable('-30 minutes'));
    }

    public function getOrgaBySid(string $sid): ?CitizenOrganization
    {
        foreach ($this->organizations as $orga) {
            if ($orga->getOrganizationSid() === $sid) {
                return $orga;
            }
        }

        return null;
    }

    public function refresh(CitizenInfos $infos, EntityManagerInterface $em): void
    {
        $this->setNickname($infos->nickname);
        $this->setBio($infos->bio);
        $this->setLastRefresh(new \DateTimeImmutable());

        foreach ($this->getOrganizations() as $orga) {
            $em->remove($orga);
        }

        $this->setMainOrga(null);
        $this->clearOrganizations();
        foreach ($infos->organizations as $orgaInfo) {
            $orga = new CitizenOrganization(Uuid::uuid4());
            $orga->setCitizen($this);
            $orga->setOrganizationSid($orgaInfo->sid->getSid());
            $orga->setRank($orgaInfo->rank);
            $orga->setRankName($orgaInfo->rankName);
            $this->addOrganization($orga);
            if ($infos->mainOrga === $orgaInfo) {
                $this->setMainOrga($orga);
            }
        }
    }
}
