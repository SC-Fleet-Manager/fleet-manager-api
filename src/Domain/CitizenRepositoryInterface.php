<?php

namespace App\Domain;

interface CitizenRepositoryInterface
{
    public function getByHandle(HandleSC $handle): ?Citizen;

    public function create(Citizen $citizen): void;

    public function update(Citizen $citizen): void;

    /**
     * @return iterable|Citizen[]
     */
    public function getByOrganisation(Trigram $organisationTrigram): iterable;
}
