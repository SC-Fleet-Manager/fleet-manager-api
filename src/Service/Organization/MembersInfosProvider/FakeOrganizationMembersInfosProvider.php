<?php

namespace App\Service\Organization\MembersInfosProvider;

use App\Domain\SpectrumIdentification;
use App\Service\Dto\RsiOrgaMemberInfos;

class FakeOrganizationMembersInfosProvider implements OrganizationMembersInfosProviderInterface
{
    public function retrieveInfos(SpectrumIdentification $sid, bool $cache = true): array
    {
        return ['visibleCitizens' => [
            new RsiOrgaMemberInfos('ionni', 'Ioni', null, 1, 'Soldat'),
            new RsiOrgaMemberInfos('ashuvidz', 'Ashuvidz', null, 5, 'Boss'),
        ], 'countHiddenCitizens' => 3];
    }

    public function getTotalMembers(SpectrumIdentification $sid, bool $cache = true): int
    {
        return 5;
    }
}
