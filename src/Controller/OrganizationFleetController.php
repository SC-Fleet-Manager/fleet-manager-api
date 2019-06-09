<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Service\Dto\ShipFamilyFilter;
use App\Service\FleetOrganizationGuard;
use App\Service\OrganizationFleetGenerator;
use App\Service\OrganizationFleetHandler;
use App\Service\ShipInfosProviderInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api", name="orga_fleet_")
 */
class OrganizationFleetController extends AbstractController
{
    private $security;
    private $citizenRepository;
    private $shipInfosProvider;
    private $organizationFleetGenerator;
    private $fleetOrganizationGuard;
    private $logger;

    public function __construct(
        Security $security,
        CitizenRepository $citizenRepository,
        ShipInfosProviderInterface $shipInfosProvider,
        OrganizationFleetGenerator $organizationFleetGenerator,
        FleetOrganizationGuard $fleetOrganizationGuard,
        LoggerInterface $logger
    ) {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
        $this->shipInfosProvider = $shipInfosProvider;
        $this->organizationFleetGenerator = $organizationFleetGenerator;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->logger = $logger;
    }

    /**
     * Combines all last version fleets of all citizen members of a specific organization.
     * Returns a downloadable json file.
     *
     * @Route("/create-organization-fleet-file/{organization}", name="create_organization_fleet_file", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function createOrganizationFleetFile(string $organization): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            throw $this->createNotFoundException(sprintf('The user "%s" has no citizens.', $user->getId()));
        }
        if (!$citizen->hasOrganization($organization)) {
            throw $this->createNotFoundException(sprintf('The citizen "%s" does not have the organization "%s".', $citizen->getId(), $organization));
        }

        $file = $this->organizationFleetGenerator->generateFleetFile(new SpectrumIdentification($organization));

        $fileResponse = $this->file($file, 'organization_fleet.json');
        $fileResponse->headers->set('Content-Type', 'application/json');
        $fileResponse->deleteFileAfterSend();

        return $fileResponse;
    }

    /**
     * @Route("/fleet/orga-fleets/{organization}", name="orga_fleets", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleets(Request $request, string $organization, OrganizationFleetHandler $organizationFleetHandler): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organization)) {
            return $response;
        }

        $shipFamilyFilter = $this->getShipFamilyFilter($request, $organization);

        $shipFamilies = $organizationFleetHandler->computeShipFamilies(new SpectrumIdentification($organization), $shipFamilyFilter);
        usort($shipFamilies, static function (array $shipFamily1, array $shipFamily2): int {
            $count = $shipFamily2['count'] - $shipFamily1['count'];
            if ($count !== 0) {
                return $count;
            }

            return $shipFamily2['name'] <=> $shipFamily1['name'];
        });

        return $this->json($shipFamilies);
    }

    /**
     * @Route("/fleet/orga-fleets/{organization}/admins", name="orga_fleets_admins", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleetsAdmins(string $organization): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organization)) {
            return $response;
        }

        $admins = $this->citizenRepository->findAdminByOrganization($organization);

        return $this->json($admins, 200, [], ['groups' => 'orga_fleet_admin']);
    }

    /**
     * @Route("/fleet/orga-fleets/{organization}/{chassisId}", name="orga_fleet_family", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleetFamily(Request $request, string $organization, string $chassisId): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organization)) {
            return $response;
        }

        $shipFamilyFilter = $this->getShipFamilyFilter($request, $organization);

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
            $countOwnersAndOwned = $this->citizenRepository->countOwnersAndOwnedOfShip($organization, $shipName, $shipFamilyFilter)[0];
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

    /**
     * @Route("/fleet/orga-fleets/{organization}/users/{providerShipName}", name="orga_fleet_users", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleetUsers(Request $request, string $organization, string $providerShipName): Response
    {
        $page = $request->query->getInt('page', 1);
        $itemsPerPage = 10;

        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organization)) {
            return $response;
        }

        $shipFamilyFilter = $this->getShipFamilyFilter($request, $organization);

        $shipName = $this->shipInfosProvider->transformProviderToHangar($providerShipName);
        $shipInfo = $this->shipInfosProvider->getShipByName($providerShipName);
        if ($shipInfo === null) {
            $this->logger->warning('Ship not found in the ship infos provider.', ['hangarShipName' => $providerShipName, 'provider' => get_class($this->shipInfosProvider)]);

            return $this->json([]);
        }

        // filtering
        if (count($shipFamilyFilter->shipSizes) > 0 && !in_array($shipInfo->size, $shipFamilyFilter->shipSizes, false)) {
            return $this->json([]);
        }
        if ($shipFamilyFilter->shipStatus !== null && $shipFamilyFilter->shipStatus !== $shipInfo->productionStatus) {
            return $this->json([]);
        }

        $loggedCitizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $loggedCitizen = $this->getUser()->getCitizen();
        }

        $countOwners = $this->citizenRepository->countOwnersOfShip($organization, $shipName, $loggedCitizen, $shipFamilyFilter);
        $users = $this->citizenRepository->getOwnersOfShip(
            $organization,
            $shipName,
            $loggedCitizen,
            $shipFamilyFilter,
            $page,
            $itemsPerPage
        );
        $lastPage = (int) ceil($countOwners / $itemsPerPage);

        return $this->json([
            'users' => $users,
            'page' => $page,
            'lastPage' => $lastPage > 0 ? $lastPage : 1,
            'total' => $countOwners,
        ], 200, [], ['groups' => 'orga_fleet']);
    }

    /**
     * @Route("/fleet/orga-fleets/{organization}/hidden-users/{providerShipName}", name="orga_fleet_hidden_users", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleetHiddenUsers(string $organization, string $providerShipName): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organization)) {
            return $response;
        }

        $shipName = $this->shipInfosProvider->transformProviderToHangar($providerShipName);
        $shipInfo = $this->shipInfosProvider->getShipByName($providerShipName);
        if ($shipInfo === null) {
            $this->logger->warning('Ship not found in the ship infos provider.', ['hangarShipName' => $providerShipName, 'provider' => get_class($this->shipInfosProvider)]);

            return $this->json([]);
        }

        $loggedCitizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $loggedCitizen = $this->getUser()->getCitizen();
        }

        $totalHiddenOwners = $this->citizenRepository->countHiddenOwnersOfShip($organization, $shipName, $loggedCitizen);

        return $this->json([
            'hiddenUsers' => $totalHiddenOwners,
        ]);
    }

    private function getShipFamilyFilter($request, string $organizationSid): ShipFamilyFilter
    {
        $filters = $request->query->get('filters', []);

        $shipFamilyFilter = new ShipFamilyFilter(
            $filters['shipNames'] ?? [],
            $filters['citizenIds'] ?? [],
            $filters['shipSizes'] ?? [],
            $filters['shipStatus'] ?? null
        );

        // remove non visibles filters (e.g., private citizens, etc.)
        $loggedCitizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $loggedCitizen = $user->getCitizen();
        }
        $visibleCitizens = $this->citizenRepository->findVisiblesByOrganization($organizationSid, $loggedCitizen);
        $shipFamilyFilter->citizenIds = array_filter($shipFamilyFilter->citizenIds, static function (string $citizenId) use (&$visibleCitizens): bool {
            foreach ($visibleCitizens as $visibleCitizen) {
                if ($citizenId === $visibleCitizen->getId()->toString()) {
                    return true;
                }
            }

            return false;
        });

        return $shipFamilyFilter;
    }
}
