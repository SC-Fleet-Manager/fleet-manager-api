<?php

namespace App\Service;

use App\Domain\SpectrumIdentification;

interface OrganizationMembersInfosProviderInterface
{
    public function retrieveInfos(SpectrumIdentification $sid, bool $cache = true): array;

    public function getTotalMembers(SpectrumIdentification $sid, bool $cache = true): int;
}
