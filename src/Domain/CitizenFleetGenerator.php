<?php

namespace App\Domain;

class CitizenFleetGenerator implements CitizenFleetGeneratorInterface
{
    /**
     * @var CitizenRepositoryInterface
     */
    private $citizenRepository;

    public function __construct(CitizenRepositoryInterface $citizenRepository)
    {
        $this->citizenRepository = $citizenRepository;
    }

    public function generateFleetFile(CitizenNumber $number): \SplFileInfo
    {
        $citizen = $this->citizenRepository->getByNumber($number);
        if ($citizen === null) {
            throw new \RuntimeException(sprintf('No citizen found for the number %s.', $number));
        }

        $fleetData = [];
        $fleet = $citizen->getLastVersionFleet();
        if ($fleet !== null) {
            $fleetData = $fleet->createRawData();
        }

        $jsonFleet = \json_encode($fleetData);
        $file = new \SplFileObject(sys_get_temp_dir().'/'.uniqid('', true), 'w');
        $file->fwrite($jsonFleet);

        return $file;
    }
}
