<?php

namespace App\Service;

use App\Domain\CitizenInfos;
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

    public function retrieveInfos(HandleSC $handleSC): CitizenInfos
    {
        $ci = new CitizenInfos(
            clone $this->citizen->getNumber(),
            clone $this->citizen->getActualHandle()
        );
        $ci->organisations = [];
        foreach ($this->citizen->getOrganisations() as $sid) {
            $ci->organisations[] = new SpectrumIdentification($sid);
        }
        $ci->bio = $this->citizen->getBio();
        $ci->avatarUrl = 'http://example.com/fake-avatar.png';
        $ci->registered = new \DateTimeImmutable('2018-01-01 12:00:00');

        return $ci;
    }
}
