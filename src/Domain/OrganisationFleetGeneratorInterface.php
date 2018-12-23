<?php

namespace App\Domain;

interface OrganisationFleetGeneratorInterface
{
    public function generateFleetFile(SpectrumIdentification $organisationTrigram): \SplFileInfo;
}
