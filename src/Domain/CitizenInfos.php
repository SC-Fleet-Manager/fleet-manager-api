<?php

namespace App\Domain;

class CitizenInfos
{
    /**
     * @var CitizenNumber
     */
    public $numberSC;

    /**
     * @var string
     */
    public $nickname;

    /**
     * @var HandleSC
     */
    public $handle;

    /**
     * @var iterable|CitizenOrganizationInfo[]
     */
    public $organizations;

    /**
     * @var CitizenOrganizationInfo
     */
    public $mainOrga;

    /**
     * @var string
     */
    public $avatarUrl;

    /**
     * @var bool
     */
    public $registered;

    /**
     * @var string
     */
    public $bio;

    public function __construct(CitizenNumber $numberSC, HandleSC $handle)
    {
        $this->numberSC = $numberSC;
        $this->handle = $handle;
        $this->organizations = [];
    }
}
