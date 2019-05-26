<?php

namespace App\Domain;

class OrganizationInfos
{
    /** @var string */
    public $fullname;

    /** @var SpectrumIdentification */
    public $spectrumId;

    /** @var string|null */
    public $avatarUrl;

    public function __construct(string $fullname, SpectrumIdentification $spectrumId, ?string $avatarUrl)
    {
        $this->fullname = $fullname;
        $this->spectrumId = $spectrumId;
        $this->avatarUrl = $avatarUrl;
    }
}
