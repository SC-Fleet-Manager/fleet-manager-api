<?php

namespace App\Service;

use App\Domain\CitizenNumber;
use App\Entity\Citizen;
use App\Repository\CitizenRepository;

class CitizenFleetGenerator
{
    private $citizenRepository;

    public function __construct(CitizenRepository $citizenRepository)
    {
        $this->citizenRepository = $citizenRepository;
    }

    public function generateFleetFile(CitizenNumber $number): \SplFileInfo
    {
        /** @var Citizen|null $citizen */
        $citizen = $this->citizenRepository->findOneBy(['number' => $number]);
        if ($citizen === null) {
            throw new \RuntimeException(sprintf('No citizen found for the number %s.', $number));
        }

        $fleetData = [];
        $fleet = $citizen->getLastFleet();
        if ($fleet !== null) {
            $fleetData = $fleet->createRawData();
        }

        $jsonFleet = \json_encode($fleetData);
        $file = new \SplFileObject(sys_get_temp_dir().'/'.uniqid('', true), 'w');
        $file->fwrite($jsonFleet);

        return $file;
    }
}
