<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Organization\MembersInfosProvider\OrganizationMembersInfosProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class StatsCitizensController extends AbstractController
{
    private Security $security;
    private OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider;
    private FleetOrganizationGuard $fleetOrganizationGuard;
    private CitizenRepository $citizenRepository;

    public function __construct(
        Security $security,
        OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider,
        FleetOrganizationGuard $fleetOrganizationGuard,
        CitizenRepository $citizenRepository
    ) {
        $this->security = $security;
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
        $maxCountShips = 0;
        $viewBestCitizen = null;
        if ($citizenMostShips !== null) {
            $maxCountShips = $citizenMostShips['maxShip'];
            /** @var Citizen $bestCitizen */
            $bestCitizen = $citizenMostShips[0];
            $viewBestCitizen = [
                'id' => $bestCitizen->getId(),
                'handle' => $bestCitizen->getActualHandle()->getHandle(),
            ];
            $orga = $bestCitizen->getOrgaBySid($organizationSid);
            if ($orga !== null) {
                $myself = false;
                /** @var User $user */
                $user = $this->security->getUser();
                if ($user !== null && $user->getCitizen() !== null && $user->getCitizen()->getId()->equals($bestCitizen->getId())) {
                    $myself = true;
                }
                if (!$myself && $orga->getVisibility() === CitizenOrganization::VISIBILITY_PRIVATE) {
                    $viewBestCitizen['handle'] = 'Anonymous';
                } elseif ($orga->getVisibility() === CitizenOrganization::VISIBILITY_ADMIN
                    && !$this->security->isGranted('IS_ADMIN_MANAGEABLE', new SpectrumIdentification($organizationSid))) {
                    $viewBestCitizen['handle'] = 'Anonymous';
                }
            }
        }

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
                'citizen' => $viewBestCitizen,
                'countShips' => $maxCountShips,
            ],
            'chartShipsPerCitizen' => [
                'xAxis' => $chartXAxis,
                'yAxis' => $chartYAxis,
            ],
        ], 200, [], ['groups' => 'orga_fleet']);
    }
}
