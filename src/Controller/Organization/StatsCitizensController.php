<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Repository\CitizenRepository;
use App\Service\FleetOrganizationGuard;
use App\Service\OrganizationMembersInfosProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsCitizensController extends AbstractController
{
    private $organizationMembersInfosProvider;
    private $fleetOrganizationGuard;
    private $citizenRepository;

    public function __construct(OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider, FleetOrganizationGuard $fleetOrganizationGuard, CitizenRepository $citizenRepository)
    {
        $this->organizationMembersInfosProvider = $organizationMembersInfosProvider;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->citizenRepository = $citizenRepository;
    }

    /**
     * @Route("/api/organization/{organizationSid}/stats/citizens", name="organization_stats_citizens", methods={"GET"})
     */
    public function __invoke(string $organizationSid): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        // How many Citizens in Orga
        $countCitizens = $this->citizenRepository->statCountCitizensByOrga(new SpectrumIdentification($organizationSid));

        // Average Ships per Citizens
        $averageShipsPerCitizen = $this->citizenRepository->statAverageShipsPerCitizenByOrga(new SpectrumIdentification($organizationSid));

        // Citizen with most Ships
        $citizenMostShips = $this->citizenRepository->statCitizenWithMostShipsByOrga(new SpectrumIdentification($organizationSid));
        $maxCountShips = $citizenMostShips !== null ? $citizenMostShips['maxShip'] : 0;

        // Column bars of number of owned ships per citizens : x Number of Ships y number of citizens.
        $shipsPerCitizen = $this->citizenRepository->statShipsPerCitizenByOrga(new SpectrumIdentification($organizationSid));
        $chartXAxis = range(1, $maxCountShips > 10 ? $maxCountShips : 10); // 1 to <max ships by citizen>
        $chartYAxis = array_fill(0, $maxCountShips > 10 ? $maxCountShips : 10, 0); // how many citizen have X ships
        foreach ($shipsPerCitizen as $citizenShips) {
            $countShips = (int) $citizenShips['countShips'];
            if ($countShips <= 0) {
                continue;
            }
            ++$chartYAxis[$countShips - 1];
        }

        $totalMembers = $this->organizationMembersInfosProvider->getTotalMembers(new SpectrumIdentification($organizationSid));

        return $this->json([
            'countCitizens' => $countCitizens,
            'totalMembers' => $totalMembers,
            'averageShipsPerCitizen' => $averageShipsPerCitizen,
            'citizenMostShips' => [
                'citizen' => $citizenMostShips[0] ?? null,
                'countShips' => $maxCountShips,
            ],
            'chartShipsPerCitizen' => [
                'xAxis' => $chartXAxis,
                'yAxis' => $chartYAxis,
            ],
        ], 200, [], ['groups' => 'orga_fleet']);
    }
}
