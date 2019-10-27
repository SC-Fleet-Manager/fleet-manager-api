<?php

namespace App\Service\Organization\InfosProvider;

use App\Domain\OrganizationInfos;
use App\Domain\SpectrumIdentification;

interface OrganizationInfosProviderInterface
{
    public function retrieveInfos(SpectrumIdentification $sid): OrganizationInfos;
}
