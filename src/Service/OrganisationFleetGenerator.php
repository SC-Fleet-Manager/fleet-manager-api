<?php

namespace App\Service;

use App\Domain\SpectrumIdentification;
use App\Repository\CitizenRepository;

class OrganisationFleetGenerator
{
    private $citizenRepository;

    public function __construct(CitizenRepository $citizenRepository)
    {
        $this->citizenRepository = $citizenRepository;
    }

    public function generateFleetFile(SpectrumIdentification $organisationTrigram): \SplFileInfo
    {
        $citizens = $this->citizenRepository->getByOrganisation($organisationTrigram);

        $orgaFleetData = [[]];
        foreach ($citizens as $citizen) {
            $fleet = $citizen->getLastVersionFleet();
            if ($fleet === null) {
                continue;
            }
            $orgaFleetData[] = $fleet->createRawData();
        }
        $orgaFleetData = array_merge(...$orgaFleetData);
        $jsonFleet = \json_encode($orgaFleetData);
        $file = new \SplFileObject(sys_get_temp_dir().'/'.uniqid('', true), 'w');
        $file->fwrite($jsonFleet);

        return $file;
    }
}
