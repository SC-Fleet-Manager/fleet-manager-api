<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository\Serializer;

use App\Domain\Citizen as DomainCitizen;
use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Domain\SpectrumIdentification;
use App\Infrastructure\Entity\Citizen;

class CitizenSerializer
{
    public function toDomain(?Citizen $citizenEntity): DomainCitizen
    {
        if ($citizenEntity === null) {
            return new DomainCitizen(null); // null-object
        }
        $citizen = new DomainCitizen($citizenEntity->id);
        $citizen->number = new CitizenNumber($citizenEntity->number);
        $citizen->actualHandle = new HandleSC($citizenEntity->actualHandle);
        $citizen->bio = $citizenEntity->bio;
        foreach ($citizenEntity->organisations as $orga) {
            $citizen->organisations[] = new SpectrumIdentification($orga);
        }

        return $citizen;
    }

    public function fromDomain(DomainCitizen $citizen): Citizen
    {
        $e = new Citizen();
        $e->id = clone $citizen->id;
        $e->number = (string) $citizen->number;
        $e->actualHandle = (string) $citizen->actualHandle;
        $e->bio = $citizen->bio;
        foreach ($citizen->organisations as $orga) {
            $e->organisations[] = (string) $orga;
        }

        return $e;
    }
}
