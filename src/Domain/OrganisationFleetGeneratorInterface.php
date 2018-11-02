<?php

namespace App\Domain;

interface OrganisationFleetGeneratorInterface
{
    public function generateFleetFile(Trigram $organisationTrigram): \SplFileInfo;
}
