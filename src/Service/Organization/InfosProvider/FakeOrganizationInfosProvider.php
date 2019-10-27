<?php

namespace App\Service\Organization\InfosProvider;

use App\Domain\OrganizationInfos;
use App\Domain\SpectrumIdentification;

class FakeOrganizationInfosProvider implements OrganizationInfosProviderInterface
{
    /** @var OrganizationInfos[] */
    private $orgaInfos;

    public function __construct()
    {
        $this->orgaInfos = [
            'flk' => new OrganizationInfos('FallKrom', new SpectrumIdentification('flk'), null),
            'gardiens' => new OrganizationInfos('Les Gardiens', new SpectrumIdentification('gardiens'), null),
        ];
    }

    public function setOrganizationInfos(array $orgaInfos): void
    {
        $this->orgaInfos = $orgaInfos;
    }

    public function retrieveInfos(SpectrumIdentification $sid): OrganizationInfos
    {
        return $this->orgaInfos[$sid->getSid()];
    }
}
