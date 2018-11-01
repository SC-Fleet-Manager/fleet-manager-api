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
     * @var iterable|Trigram[]
     */
    public $organisations;

    public function __construct(UuidInterface $id)
    {
        $this->id = $id;
        $this->organisations = [];
    }
}
