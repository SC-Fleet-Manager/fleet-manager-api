<?php

namespace App\Controller\Organization;

use App\Domain\ShipInfo;
use App\Domain\SpectrumIdentification;
use App\Repository\OrganizationRepository;
use App\Service\FleetOrganizationGuard;
use App\Service\ShipInfosProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsShipsController extends AbstractController
{
    private $fleetOrganizationGuard;
    private $organizationRepository;
    private $shipInfosProvider;

    public function __construct(FleetOrganizationGuard $fleetOrganizationGuard, OrganizationRepository $organizationRepository, ShipInfosProviderInterface $shipInfosProvider)
    {
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->organizationRepository = $organizationRepository;
        $this->shipInfosProvider = $shipInfosProvider;
    }

    /**
     * @Route("/api/organization/{organizationSid}/stats/ships", name="organization_stats_ships", methods={"GET"})
     */
    public function __invoke(string $organizationSid): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        // How many ships in the orga
        $totalShips = $this->organizationRepository->statTotalShipsByOrga(new SpectrumIdentification($organizationSid));

        // Number of Flyable vs in concept ships
        // Total needed minimum / Maximum crew : xxx min crew - yyy max crew
        // Total SCU capacity : xxx Total SCU
        // Charts of ship size repartition
        $orgaShips = $this->organizationRepository->statShipsByOrga(new SpectrumIdentification($organizationSid));
        $countFlightReady = 0;
        $countInConcept = 0;
        $minCrew = 0;
        $maxCrew = 0;
        $cargoCapacity = 0;
        $chartShipSizes = [
            ShipInfo::SIZE_VEHICLE => 0,
            ShipInfo::SIZE_SNUB => 0,
            ShipInfo::SIZE_SMALL => 0,
            ShipInfo::SIZE_MEDIUM => 0,
            ShipInfo::SIZE_LARGE => 0,
            ShipInfo::SIZE_CAPITAL => 0,
        ];

        foreach ($orgaShips as $orgaShip) {
            if ($orgaShip === null) {
                continue;
            }
            $shipName = $this->shipInfosProvider->transformHangarToProvider($orgaShip->getName());
            $shipInfo = $this->shipInfosProvider->getShipByName($shipName);
            if ($shipInfo === null) {
                continue;
            }
            if ($shipInfo->productionStatus === ShipInfo::FLIGHT_READY) {
                ++$countFlightReady;
            } else {
                ++$countInConcept;
            }
            $minCrew += $shipInfo->minCrew;
            $maxCrew += $shipInfo->maxCrew;
            $cargoCapacity += $shipInfo->cargoCapacity;
            if (in_array($shipInfo->size, ShipInfo::SIZES, true)) {
                ++$chartShipSizes[$shipInfo->size];
            }
        }

        return $this->json([
            'countShips' => $totalShips,
            'countFlightReady' => $countFlightReady,
            'countInConcept' => $countInConcept,
            'minCrew' => $minCrew,
            'maxCrew' => $maxCrew,
            'cargoCapacity' => $cargoCapacity,
            'chartShipSizes' => [
                'xAxis' => array_keys($chartShipSizes),
                'yAxis' => array_values($chartShipSizes),
            ],
        ]);
    }
}
