<?php

namespace App\Domain;

use Ramsey\Uuid\UuidInterface;

class Citizen
{
    /**
     * @var UuidInterface
     */
    public $id;

    /**
     * @var CitizenNumber
     */
    public $number;

    /**
     * @var HandleSC
     */
    public $actualHandle;

    /**
     * @var iterable|SpectrumIdentification[]
     */
    public $organisations;

    /**
     * @var iterable|Fleet[]
     */
    public $fleets;

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
