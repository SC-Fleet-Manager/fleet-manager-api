<?php

namespace App\Service;

use App\Domain\OrganizationInfos;
use App\Domain\SpectrumIdentification;

interface OrganizationInfosProviderInterface
{
    public function retrieveInfos(SpectrumIdentification $sid): OrganizationInfos;
}
