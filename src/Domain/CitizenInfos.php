<?php

namespace App\Domain;

class CitizenInfos
{
    /**
     * @var CitizenNumber
     */
    public $numberSC;

    /**
     * @var HandleSC
     */
    public $handle;

    /**
     * @var iterable|Trigram[]
     */
    public $organisations;

    /**
     * @var string
     */
    public $avatarUrl;

    /**
     * @var bool
     */
    public $registered;

    public function __construct(CitizenNumber $numberSC, HandleSC $handle)
    {
        $this->numberSC = $numberSC;
        $this->handle = $handle;
        $this->organisations = [];
    }
}
