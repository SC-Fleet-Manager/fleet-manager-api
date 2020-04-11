<?php

namespace App\Controller\Organization\Fleet;

use App\Repository\CitizenRepository;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Organization\ShipFamilyFilterFactory;
use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Ramsey\Uuid\Uuid;
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

        $shipInfos = $this->shipInfosProvider->getShipsByChassisId($chassisId);

        $res = [];
        foreach ($shipInfos as $shipInfo) {
            if ($shipInfo->id === null) {
                continue;
            }
            // filtering
            if (count($shipFamilyFilter->shipSizes) > 0 && !in_array($shipInfo->size, $shipFamilyFilter->shipSizes, false)) {
                continue;
            }
            if ($shipFamilyFilter->shipStatus !== null && $shipFamilyFilter->shipStatus !== $shipInfo->productionStatus) {
                continue;
            }

            $countShip = $this->citizenRepository->countShipOwnedByOrga($organizationSid, Uuid::fromString($shipInfo->id), $shipFamilyFilter)[0];
            if ((int) $countShip['countShips'] === 0) {
                // only return owner ships.
                continue;
            }
            $res[] = [
                'shipInfo' => $shipInfo,
                'countTotalShips' => (int) $countShip['countShips'],
            ];
        }
        usort($res, static function (array $result1, array $result2): int {
            return (int) $result2['countTotalShips'] - (int) $result1['countTotalShips'];
        });

        return $this->json($res);
    }
}
