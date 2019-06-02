<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use App\Service\Dto\ShipFamilyFilter;
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
 * @Route("/api/fleet", name="fleet_")
 */
class FleetController extends AbstractController
{
    private $security;
    private $citizenRepository;
    private $userRepository;
    private $shipInfosProvider;
    private $organizationRepository;
    private $logger;

    public function __construct(
        Security $security,
        CitizenRepository $citizenRepository,
        UserRepository $userRepository,
        ShipInfosProviderInterface $shipInfosProvider,
        OrganizationRepository $organizationRepository,
        LoggerInterface $logger
    ) {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
        $this->userRepository = $userRepository;
        $this->shipInfosProvider = $shipInfosProvider;
        $this->organizationRepository = $organizationRepository;
        $this->logger = $logger;
    }

    /**
     * @Route("/my-fleet", name="my_fleet", methods={"GET"}, options={"expose":true})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function myFleet(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }
        $fleet = $citizen->getLastVersionFleet();
        $shipInfos = $this->shipInfosProvider->getAllShips();

        return $this->json([
            'fleet' => $fleet,
            'shipInfos' => $shipInfos,
        ], 200, [], ['groups' => ['my-fleet']]);
    }

    /**
     * @Route("/user-fleet/{handle}", name="user_fleet", methods={"GET"}, options={"expose":true})
     */
    public function userFleet(string $handle): Response
    {
        /** @var Citizen|null $citizen */
        $citizen = $this->citizenRepository->findOneBy(['actualHandle' => $handle]);
        if ($citizen === null) {
            throw $this->createNotFoundException(sprintf('Citizen %s does not exist.', $handle));
        }

        /** @var User|null $me */
        $me = $this->getUser();

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['citizen' => $citizen]);
        if ($user === null) {
            throw $this->createNotFoundException(sprintf('User of citizen %s does not exist.', $handle));
        }

        // TODO : make a voter
        if ($user->getPublicChoice() === User::PUBLIC_CHOICE_PRIVATE
            && (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED') || !$me->getId()->equals($user->getId()))) {
            return $this->json([
                'error' => 'no_rights',
                'errorMessage' => 'You have no rights to see this fleet.',
            ], 403);
        }
        if ($user->getPublicChoice() === User::PUBLIC_CHOICE_ORGANIZATION
            && (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')
                || $me->getCitizen() === null
                || empty(array_intersect($citizen->getOrganisations(), $me->getCitizen()->getOrganisations())))) {
            return $this->json([
                'error' => 'no_rights',
                'errorMessage' => 'You have no rights to see this fleet.',
            ], 403);
        }

        $fleet = $citizen->getLastVersionFleet();
        $shipInfos = $this->shipInfosProvider->getAllShips();

        return $this->json([
            'fleet' => $fleet,
            'shipInfos' => $shipInfos,
        ], 200, [], ['groups' => ['public-fleet']]);
    }

    /**
     * @Route("/orga-fleets/{organization}", name="orga_fleets", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleets(Request $request, string $organization, OrganizationFleetHandler $organizationFleetHandler): Response
    {
        if (!$this->isPublicOrga($organization)) {
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
            if (!$citizen->hasOrganisation($organization)) {
                return $this->json([
                    'error' => 'bad_organization',
                    'errorMessage' => sprintf('The organization %s does not exist.', $organization),
                ], 404);
            }
        }

        $filters = $request->query->get('filters', []);
        $shipFamilyFilter = new ShipFamilyFilter(
            $filters['shipNames'] ?? [],
            $filters['citizenIds'] ?? [],
            $filters['shipSizes'] ?? [],
        );

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
     * @Route("/orga-fleets/{organization}/{chassisId}", name="orga_fleet_family", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleetFamily(Request $request, string $organization, string $chassisId): Response
    {
        if (!$this->isPublicOrga($organization)) {
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
            if (!$citizen->hasOrganisation($organization)) {
                return $this->json([
                    'error' => 'bad_organization',
                    'errorMessage' => sprintf('The organization %s does not exist.', $organization),
                ], 404);
            }
        }

        $filters = $request->query->get('filters', []);
        $shipFamilyFilter = new ShipFamilyFilter(
            $filters['shipNames'] ?? [],
            $filters['citizenIds'] ?? [],
            $filters['shipSizes'] ?? [],
        );

        $shipsInfos = $this->shipInfosProvider->getShipsByChassisId($chassisId);

        $res = [];
        foreach ($shipsInfos as $shipInfo) {
            // filtering
            if (count($shipFamilyFilter->shipSizes) > 0 && !in_array($shipInfo->size, $shipFamilyFilter->shipSizes, false)) {
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
     * @Route("/orga-fleets/{organization}/users/{providerShipName}", name="orga_fleet_users", methods={"GET"}, options={"expose":true})
     */
    public function orgaFleetUsers(Request $request, string $organization, string $providerShipName): Response
    {
        $page = $request->query->get('page', 1);
        $itemsPerPage = 10;

        if (!$this->isPublicOrga($organization)) {
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
            if (!$citizen->hasOrganisation($organization)) {
                return $this->json([
                    'error' => 'bad_organization',
                    'errorMessage' => sprintf('The organization %s does not exist.', $organization),
                ], 404);
            }
        }

        $filters = $request->query->get('filters', []);
        $shipFamilyFilter = new ShipFamilyFilter(
            $filters['shipNames'] ?? [],
            $filters['citizenIds'] ?? [],
            $filters['shipSizes'] ?? [],
        );

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

        $users = $this->citizenRepository->getOwnersOfShip(
            $organization,
            $shipName,
            $shipFamilyFilter,
            $page,
            $itemsPerPage,
        );

        return $this->json($users, 200, [], ['groups' => 'orga_fleet']);
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
