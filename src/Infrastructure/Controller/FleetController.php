<?php

namespace App\Infrastructure\Controller;

use App\Domain\Citizen;
use App\Domain\CitizenRepositoryInterface;
use App\Domain\Ship;
use App\Domain\ShipInfo;
use App\Domain\ShipInfosProviderInterface;
use App\Domain\SpectrumIdentification;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FleetController extends AbstractController
{
    /**
     * @Route("/fleets/{organisation}", name="fleets", options={"expose":true})
     */
    public function fleets(
        Request $request,
        string $organisation,
        CitizenRepositoryInterface $citizenRepository,
        ShipInfosProviderInterface $shipInfosProvider): Response
    {
        $citizens = $citizenRepository->getByOrganisation(new SpectrumIdentification($organisation));
        $citizensFiltered = $citizens;

        $citizenIdsFilter = $request->query->get('citizens', null);
        if ($citizenIdsFilter !== null) {
            $citizensFiltered = \array_filter($citizensFiltered, function (Citizen $citizen) use ($citizenIdsFilter): bool {
                foreach ($citizenIdsFilter as $citizenIdFilter) {
                    if ($citizen->id->toString() === $citizenIdFilter) {
                        return true;
                    }
                }

                return false;
            });
        }

        $shipCounter = [];
        $shipInfos = $shipInfosProvider->getAllShips();
        $shipInfosFiltered = $shipInfos;
        $shipNamesFilter = $request->query->get('ships', null);
        if ($shipNamesFilter !== null) {
            $shipInfosFiltered = \array_filter($shipInfosFiltered, function (ShipInfo $shipInfo) use ($shipNamesFilter): bool {
                foreach ($shipNamesFilter as $shipNameFilter) {
                    if ($shipInfo->name === $shipNameFilter) {
                        return true;
                    }
                }

                return false;
            });
        }
        foreach ($shipInfosFiltered as $shipInfo) {
            $shipCounter[$shipInfo->name] = [];
            foreach ($citizensFiltered as $citizen) {
                $fleet = $citizen->getLastVersionFleet();
                if ($fleet === null) {
                    continue;
                }
                foreach ($fleet->ships as $ship) {
                    if ($ship->name === $shipInfo->name) {
                        $shipCounter[$shipInfo->name][$citizen->id->toString()] = ($shipCounter[$shipInfo->name][$citizen->id->toString()] ?? 0) + 1;
                    }
                }
            }
        }

        $shipInfosFiltered = \array_filter($shipInfosFiltered, function (ShipInfo $shipInfo) use (&$shipCounter): bool {
            return \count($shipCounter[$shipInfo->name]) > 0;
        });

        $tableHeaders = [
            'shipName' => [
                'label' => 'Vaisseaux',
                'sortable' => true,
            ],
            'shipManufacturer' => [
                'label' => 'Constructeurs',
                'sortable' => true,
            ],
            'totalAvailable' => [
                'label' => 'Total dispo',
                'sortable' => true,
            ],
        ];
        foreach ($citizensFiltered as $citizen) {
            $tableHeaders[(string) $citizen->actualHandle] = [
                'label' => (string) $citizen->actualHandle,
                'sortable' => true,
            ];
        }

        $viewFleets = [];
        foreach ($shipInfosFiltered as $shipInfo) {
            $viewFleet = [
                '_cellVariants' => ['shipName' => $shipInfo->productionStatus === ShipInfo::FLIGHT_READY ? 'success' : 'danger'],
                'shipName' => $shipInfo->name,
                'shipManufacturer' => $shipInfo->manufacturerCode,
                'totalAvailable' => \count($shipCounter[$shipInfo->name]),
            ];
            foreach ($citizensFiltered as $citizen) {
                $count = $shipCounter[$shipInfo->name][$citizen->id->toString()] ?? null;
                $viewFleet[(string) $citizen->actualHandle] = $count;
                $viewFleet['_cellVariants'][(string) $citizen->actualHandle] = $count ? 'success' : '';
            }
            $viewFleets[] = $viewFleet;
        }

        $viewShips = [];
        foreach ($shipInfos as $shipInfo) {
            $viewShips[$shipInfo->name] = $shipInfo->name;
        }

        $viewCitizens = [];
        foreach ($citizens as $citizen) {
            $viewCitizens[$citizen->id->toString()] = (string) $citizen->actualHandle;
        }

        return $this->json([
            'tableHeaders' => $tableHeaders,
            'fleets' => $viewFleets,
            'ships' => $viewShips,
            'citizens' => $viewCitizens,
            'shipInfos' => $shipInfosFiltered,
        ]);
    }
}
