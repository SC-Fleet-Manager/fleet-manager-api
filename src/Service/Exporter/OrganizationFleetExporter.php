<?php

namespace App\Service\Exporter;

use App\Domain\SpectrumIdentification;
use App\Repository\CitizenRepository;

class OrganizationFleetExporter
{
    private $citizenRepository;

    public function __construct(CitizenRepository $citizenRepository)
    {
        $this->citizenRepository = $citizenRepository;
    }

    public function exportOrgaFleet(string $organizationSid): array
    {
        $citizens = $this->citizenRepository->getByOrganization(new SpectrumIdentification($organizationSid));

        $ships = [];
        $totalColumn = [];
        $lastFleetRow = [];
        foreach ($citizens as $citizen) {
            $citizenHandle = $citizen->getActualHandle()->getHandle();
            $lastFleet = $citizen->getLastFleet();
            if ($lastFleet === null) {
                continue;
            }
            $lastFleetRow[$citizenHandle] = $lastFleet->getUploadDate()->format('Y-m-d');
            foreach ($lastFleet->getShips() as $ship) {
                if (!isset($ships[$ship->getName()])) {
                    $ships[$ship->getName()] = [$citizenHandle => 1];
                } elseif (!isset($ships[$ship->getName()][$citizenHandle])) {
                    $ships[$ship->getName()][$citizenHandle] = 1;
                } else {
                    ++$ships[$ship->getName()][$citizenHandle];
                }
            }
        }
        ksort($ships);

        $data = [];

        // row Last Fleet Update date
        $data[] = array_merge([
            'Ship Model' => null,
            'Ship Total' => null,
        ], $lastFleetRow);

        // rows Ships
        foreach ($ships as $shipName => $owners) {
            $total = 0;
            $columns = [];
            foreach ($owners as $ownerName => $countOwner) {
                $total += $countOwner;
                $columns[$ownerName] = $countOwner;
                if (!isset($totalColumn[$ownerName])) {
                    $totalColumn[$ownerName] = $countOwner;
                } else {
                    $totalColumn[$ownerName] += $countOwner;
                }
            }
            $data[] = array_merge([
                'Ship Model' => $shipName,
                'Ship Total' => $total,
            ], $columns);
        }

        // row Total
        $total = 0;
        $columns = [];
        foreach ($totalColumn as $ownerName => $countOwner) {
            $total += $countOwner;
            $columns[$ownerName] = $countOwner;
        }
        $data[] = array_merge([
            'Ship Model' => 'Total',
            'Ship Total' => $total,
        ], $columns);

        return $data;
    }
}
