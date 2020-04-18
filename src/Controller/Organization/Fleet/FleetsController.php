<?php

namespace App\Controller\Organization\Fleet;

use App\Domain\SpectrumIdentification;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Organization\Fleet\OrganizationFleetHandler;
use App\Service\Organization\ShipFamilyFilterFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FleetsController extends AbstractController
{
    private FleetOrganizationGuard $fleetOrganizationGuard;
    private ShipFamilyFilterFactory $shipFamilyFilterFactory;
    private OrganizationFleetHandler $organizationFleetHandler;

    public function __construct(
        FleetOrganizationGuard $fleetOrganizationGuard,
        ShipFamilyFilterFactory $shipFamilyFilterFactory,
        OrganizationFleetHandler $organizationFleetHandler
    ) {
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

        try {
            $shipFamilies = $this->organizationFleetHandler->computeShipFamilies(new SpectrumIdentification($organizationSid), $shipFamilyFilter);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'unable_request_ships_infos_provider',
                'errorMessage' => 'Sorry, we\'re unable to retrieve the ship infos from SC Galaxy. Please retry in a moment.',
            ], 400);
        }

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
