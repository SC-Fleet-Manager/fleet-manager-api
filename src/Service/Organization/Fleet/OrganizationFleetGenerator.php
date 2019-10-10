<?php

namespace App\Service\Organization\Fleet;

use App\Domain\SpectrumIdentification;
use App\Repository\CitizenRepository;

class OrganizationFleetGenerator
{
    private $citizenRepository;

    public function __construct(CitizenRepository $citizenRepository)
    {
        $this->citizenRepository = $citizenRepository;
    }

    public function generateFleetFile(SpectrumIdentification $organizationSid): \SplFileInfo
    {
        $citizens = $this->citizenRepository->getByOrganization($organizationSid);

        $orgaFleetData = [[]];
        foreach ($citizens as $citizen) {
            $fleet = $citizen->getLastFleet();
            if ($fleet === null) {
                continue;
            }
            $orgaFleetData[] = $fleet->createRawData();
        }
        $orgaFleetData = array_merge(...$orgaFleetData);
        $jsonFleet = json_encode($orgaFleetData);
        $file = new \SplFileObject(sys_get_temp_dir().'/'.uniqid('', true), 'w');
        $file->fwrite($jsonFleet);

        return $file;
    }
}
