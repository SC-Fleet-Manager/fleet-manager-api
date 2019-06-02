<?php

namespace App\Service;

use App\Domain\CitizenInfos;
use App\Domain\CitizenOrganizationInfo;
use App\Domain\HandleSC;
use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;

class FakeCitizenInfosProvider implements CitizenInfosProviderInterface
{
    /** @var Citizen */
    private $citizen;

    public function setCitizen(Citizen $citizen): void
    {
        $this->citizen = $citizen;
    }

    public function retrieveInfos(HandleSC $handleSC, bool $caching = true): CitizenInfos
    {
        $sourceMainOrga = $this->citizen->getMainOrga();
        $mainOrga = null;
        if ($sourceMainOrga !== null) {
            $mainOrga = new CitizenOrganizationInfo(
                new SpectrumIdentification($sourceMainOrga->getOrganizationSid()),
                $sourceMainOrga->getRank(),
                $sourceMainOrga->getRankName()
            );
        }

        $orgas = [];
        foreach ($this->citizen->getOrganizations() as $citizenOrga) {
            $orga = new CitizenOrganizationInfo(
                new SpectrumIdentification($citizenOrga->getOrganizationSid()),
                $citizenOrga->getRank(),
                $citizenOrga->getRankName(),
            );
            $orgas[] = $orga;
        }

        $ci = new CitizenInfos(
            clone $this->citizen->getNumber(),
            clone $this->citizen->getActualHandle()
        );
        $ci->mainOrga = $mainOrga;
        $ci->organisations = [];
        if ($mainOrga !== null) {
            $ci->organisations[] = $mainOrga;
        }
        $ci->organisations = array_merge($ci->organisations, $orgas);
        $ci->bio = $this->citizen->getBio();
        $ci->avatarUrl = 'http://example.com/fake-avatar.png';
        $ci->registered = new \DateTimeImmutable('2018-01-01 12:00:00');

        return $ci;
    }
}
