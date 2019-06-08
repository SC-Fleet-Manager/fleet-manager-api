<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use App\Service\Dto\ShipFamilyFilter;
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
    private $organizationRepository;
    private $organizationFleetGenerator;
    private $logger;

    public function __construct(
        Security $security,
        CitizenRepository $citizenRepository,
        ShipInfosProviderInterface $shipInfosProvider,
        OrganizationRepository $organizationRepository,
        OrganizationFleetGenerator $organizationFleetGenerator,
        LoggerInterface $logger
    ) {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
        $this->shipInfosProvider = $shipInfosProvider;
        $this->organizationRepository = $organizationRepository;
        $this->organizationFleetGenerator = $organizationFleetGenerator;
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
        if (null !== $response = $this->checkAccessibleOrganization($organization)) {
            return $response;
        }

        $shipFamilyFilter = $this->getShipFamilyFilter($request);

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
     * @Route("/fleet/orga-fleets/{organization}/{chassisId}", name="orga_fleet_family", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleetFamily(Request $request, string $organization, string $chassisId): Response
    {
        if (null !== $response = $this->checkAccessibleOrganization($organization)) {
            return $response;
        }

        $shipFamilyFilter = $this->getShipFamilyFilter($request);

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
        $page = $request->query->get('page', 1);
        $itemsPerPage = 10;

        if (null !== $response = $this->checkAccessibleOrganization($organization)) {
            return $response;
        }

        $shipFamilyFilter = $this->getShipFamilyFilter($request);

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

        $users = $this->citizenRepository->getOwnersOfShip(
            $organization,
            $shipName,
            $shipFamilyFilter,
            $page,
            $itemsPerPage
        );

        return $this->json($users, 200, [], ['groups' => 'orga_fleet']);
    }

    private function getShipFamilyFilter($request): ShipFamilyFilter
    {
        $filters = $request->query->get('filters', []);

        return new ShipFamilyFilter(
            $filters['shipNames'] ?? [],
            $filters['citizenIds'] ?? [],
            $filters['shipSizes'] ?? [],
            $filters['shipStatus'] ?? null
        );
    }

    private function checkAccessibleOrganization(string $orgaSid): ?Response
    {
        if (!$this->isPublicOrga($orgaSid)) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

            /** @var User $user */
            $user = $this->security->getUser();
            $citizen = $user->getCitizen();
            if ($citizen === null) {
                return $this->json([
                    'error' => 'no_citizen_created',
                    'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
                ], 400);
            }
            if (!$citizen->hasOrganization($orgaSid)) {
                return $this->json([
                    'error' => 'bad_organization',
                    'errorMessage' => sprintf('The organization %s does not exist.', $orgaSid),
                ], 404);
            }
        }

        return null;
    }

    private function isPublicOrga(string $organizationSid): bool
    {
        /** @var Organization $orga */
        $orga = $this->organizationRepository->findOneBy(['organizationSid' => $organizationSid]);
        if ($orga === null) {
            return false;
        }

        return $orga->getPublicChoice() === Organization::PUBLIC_CHOICE_PUBLIC;
    }
}
