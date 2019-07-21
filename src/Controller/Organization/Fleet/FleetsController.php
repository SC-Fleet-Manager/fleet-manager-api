<?php

namespace App\Controller\Organization\Fleet;

use App\Domain\SpectrumIdentification;
use App\Service\FleetOrganizationGuard;
use App\Service\Organization\ShipFamilyFilterFactory;
use App\Service\OrganizationFleetHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FleetsController extends AbstractController
{
    private $fleetOrganizationGuard;
    private $shipFamilyFilterFactory;
    private $organizationFleetHandler;

    public function __construct(FleetOrganizationGuard $fleetOrganizationGuard, ShipFamilyFilterFactory $shipFamilyFilterFactory, OrganizationFleetHandler $organizationFleetHandler)
    {
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->shipFamilyFilterFactory = $shipFamilyFilterFactory;
        $this->organizationFleetHandler = $organizationFleetHandler;
    }

    public function __invoke(Request $request, string $organizationSid): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        $shipFamilyFilter = $this->shipFamilyFilterFactory->create($request, $organizationSid);

        $shipFamilies = $this->organizationFleetHandler->computeShipFamilies(new SpectrumIdentification($organizationSid), $shipFamilyFilter);
        usort($shipFamilies, static function (array $shipFamily1, array $shipFamily2): int {
            $count = $shipFamily2['count'] - $shipFamily1['count'];
            if ($count !== 0) {
                return $count;
            }

            return $shipFamily2['name'] <=> $shipFamily1['name'];
        });

        return $this->json($shipFamilies);
    }
}
