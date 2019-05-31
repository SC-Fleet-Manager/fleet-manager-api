<?php

namespace App\Domain;

class CitizenOrganizationInfo
{
    public $sid;
    public $rank;
    public $rankName;

    public function __construct(SpectrumIdentification $sid, int $rank, string $rankName)
    {
        $this->sid = $sid;
        $this->rank = $rank;
        $this->rankName = $rankName;
    }
}
