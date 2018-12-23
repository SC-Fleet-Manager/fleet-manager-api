<?php

namespace App\Domain;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class Citizen
{
    /**
     * @var UuidInterface
     *
     * @Groups({"profile"})
     */
    public $id;

    /**
     * @var CitizenNumber
     *
     * @Groups({"profile"})
     */
    public $number;

    /**
     * @var HandleSC
     *
     * @Groups({"profile"})
     */
    public $actualHandle;

    /**
     * @var iterable|SpectrumIdentification[]
     *
     * @Groups({"profile"})
     */
    public $organisations;

    /**
     * @var iterable|Fleet[]
     */
    public $fleets;

    /**
     * @var string
     *
     * @Groups({"profile"})
     */
    public $bio;

    public function __construct(?UuidInterface $id)
    {
        $this->id = $id;
        $this->organisations = [];
        $this->fleets = [];
    }

    public function getLastVersionFleet(): ?Fleet
    {
        $maxVersion = 0;
        $lastFleet = null;
        foreach ($this->fleets as $fleet) {
            if ($fleet->version > $maxVersion) {
                $maxVersion = $fleet->version;
                $lastFleet = $fleet;
            }
        }

        return $lastFleet;
    }
}
