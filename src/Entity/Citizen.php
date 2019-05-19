<?php

namespace App\Entity;

use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Domain\SpectrumIdentification;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CitizenRepository")
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
     * @var HandleSC
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"profile", "orga_fleet"})
     */
    private $actualHandle;

    /**
     * @var iterable|string[]
     *
     * @ORM\Column(type="json")
     * @Groups({"profile", "orga_fleet"})
     */
    private $organisations;

    /**
     * @var iterable|Fleet[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Fleet", mappedBy="owner")
     */
    private $fleets;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"profile"})
     */
    private $bio;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->organisations = [];
        $this->fleets = new ArrayCollection();
    }

    public function getLastVersionFleet(): ?Fleet
    {
        $maxVersion = 0;
        $lastFleet = null;
        foreach ($this->fleets as $fleet) {
            if ($fleet->getVersion() > $maxVersion) {
                $maxVersion = $fleet->getVersion();
                $lastFleet = $fleet;
            }
        }

        return $lastFleet;
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

    public function getActualHandle(): ?HandleSC
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

    /**
     * @return iterable|string[]
     */
    public function getOrganisations(): iterable
    {
        return $this->organisations;
    }

    public function hasOrganisation(string $sid): bool
    {
        return \in_array($sid, $this->organisations, true);
    }

    /**
     * @param string|SpectrumIdentification $sid
     */
    public function addOrganisation($sid): self
    {
        if ($sid instanceof SpectrumIdentification) {
            $sid = $sid->getSid();
        }
        if (!$this->hasOrganisation($sid)) {
            $this->organisations[] = $sid;
        }

        return $this;
    }

    public function setOrganisations(array $sids): self
    {
        foreach ($sids as $sid) {
            $this->addOrganisation($sid);
        }

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
        if (!$this->fleets->contains($fleet)) {
            $this->fleets->add($fleet);
        }

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
}
