<?php

namespace App\Service;

use App\Domain\SpectrumIdentification;

interface OrganizationMembersInfosProviderInterface
{
    public function retrieveInfos(SpectrumIdentification $sid): array;
}
