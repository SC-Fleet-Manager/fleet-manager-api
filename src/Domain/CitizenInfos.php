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
     * @var int
     */
    public $countRedactedOrganizations = 0;

    /**
     * @var CitizenOrganizationInfo
     */
    public $mainOrga;

    /**
     * @var bool
     */
    public $redactedMainOrga = false;

    /**
     * @var string
     */
    public $avatarUrl;

    /**
     * @var \DateTimeInterface
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
