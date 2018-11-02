<?php

namespace App\Domain;


class OrganisationFleetGenerator implements OrganisationFleetGeneratorInterface
{
    /**
     * @var CitizenRepositoryInterface
     */
    private $citizenRepository;

    public function __construct(CitizenRepositoryInterface $citizenRepository)
    {
        $this->citizenRepository = $citizenRepository;
    }

    public function generateFleetFile(Trigram $organisationTrigram): \SplFileInfo
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
