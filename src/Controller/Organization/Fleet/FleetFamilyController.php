<?php

namespace App\Controller\Organization\Fleet;

use App\Repository\CitizenRepository;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Organization\ShipFamilyFilterFactory;
use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FleetFamilyController extends AbstractController
{
    private FleetOrganizationGuard $fleetOrganizationGuard;
    private ShipInfosProviderInterface $shipInfosProvider;
    private CitizenRepository $citizenRepository;
    private ShipFamilyFilterFactory $shipFamilyFilterFactory;

    public function __construct(
        FleetOrganizationGuard $fleetOrganizationGuard,
        ShipInfosProviderInterface $shipInfosProvider,
        CitizenRepository $citizenRepository,
        ShipFamilyFilterFactory $shipFamilyFilterFactory
    ) {
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->shipInfosProvider = $shipInfosProvider;
        $this->citizenRepository = $citizenRepository;
        $this->shipFamilyFilterFactory = $shipFamilyFilterFactory;
    }

    public function __invoke(Request $request, string $organizationSid, string $chassisId): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        $shipFamilyFilter = $this->shipFamilyFilterFactory->create($request, $organizationSid);

        $shipsInfos = $this->shipInfosProvider->getShipsByChassisId($chassisId);

        $res = [];
        foreach ($shipsInfos as $shipInfo) {
            // filtering
            if (count($shipFamilyFilter->shipSizes) > 0 && !in_array($shipInfo->size, $shipFamilyFilter->shipSizes, false)) {
                continue;
            }
            if ($shipFamilyFilter->shipStatus !== null && $shipFamilyFilter->shipStatus !== $shipInfo->productionStatus) {
                continue;
            }
            $shipName = $this->shipInfosProvider->transformProviderToHangar($shipInfo->name);
            $countOwnersAndOwned = $this->citizenRepository->countOwnersAndOwnedOfShip($organizationSid, $shipName, $shipFamilyFilter)[0];
            if ((int) $countOwnersAndOwned['countOwned'] === 0) {
                continue;
            }
            $res[] = [
                'shipInfo' => $shipInfo,
                'countTotalOwners' => $countOwnersAndOwned['countOwners'],
                'countTotalShips' => $countOwnersAndOwned['countOwned'],
            ];
        }
        usort($res, static function (array $result1, array $result2): int {
            return $result2['countTotalShips'] - $result1['countTotalShips'];
        });

        return $this->json($res);
    }
}
