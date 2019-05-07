<?php

namespace App\Service;

use App\Domain\CitizenInfos;
use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Domain\SpectrumIdentification;

class FakeCitizenInfosProvider implements CitizenInfosProviderInterface
{
    public function retrieveInfos(HandleSC $handleSC): CitizenInfos
    {
        $ci = new CitizenInfos(
            new CitizenNumber('000000'),
            clone $handleSC
        );
        $ci->organisations = [
            new SpectrumIdentification('fak'),
        ];
        $ci->avatarUrl = 'http://example.com/fake-avatar.png';
        $ci->registered = new \DateTimeImmutable('2018-01-01 12:00:00');

        return $ci;
    }
}
