<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\UserRepository;
use App\Service\OrganizationFleetHandler;
use App\Service\ShipInfosProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class FleetController extends AbstractController
{
    private $security;
    private $citizenRepository;
    private $userRepository;
    private $shipInfosProvider;

    public function __construct(
        Security $security,
        CitizenRepository $citizenRepository,
        UserRepository $userRepository,
        ShipInfosProviderInterface $shipInfosProvider
    ) {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
        $this->userRepository = $userRepository;
        $this->shipInfosProvider = $shipInfosProvider;
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
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/#/profile">profile page</a>.',
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
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function orgaFleets(Request $request, string $organization, OrganizationFleetHandler $organizationFleetHandler): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/#/profile">profile page</a>.',
            ], 400);
        }
        if (!$citizen->hasOrganisation($organization)) {
            return $this->json([
                'error' => 'bad_organisation',
                'errorMessage' => sprintf('The organisation %s does not exist.', $organization),
            ], 404);
        }

        $shipFamilies = $organizationFleetHandler->computeShipFamilies(new SpectrumIdentification($organization));
        usort($shipFamilies, static function (array $shipFamily1, array $shipFamily2): int {
            return $shipFamily2['count'] - $shipFamily1['count'];
        });

        return $this->json($shipFamilies);
    }

    /**
     * @Route("/orga-fleets/{organization}/{chassisId}", name="orga_fleet_family", methods={"GET"}, options={"expose":true})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function orgaFleetFamily(Request $request, string $organization, string $chassisId): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/#/profile">profile page</a>.',
            ], 400);
        }
        if (!$citizen->hasOrganisation($organization)) {
            return $this->json([
                'error' => 'bad_organisation',
                'errorMessage' => sprintf('The organisation %s does not exist.', $organization),
            ], 404);
        }

        $shipsInfos = $this->shipInfosProvider->getShipsByChassisId($chassisId);

        $res = [];
        foreach ($shipsInfos as $shipInfo) {
            $shipName = $this->shipInfosProvider->transformProviderToHangar($shipInfo->name);
            $countOwners = $this->citizenRepository->countOwnersOfShip($organization, $shipName);
            $countOwned = $this->citizenRepository->countOwnedShips($organization, $shipName);
            $res[] = [
                'shipInfo' => $shipInfo,
                'countTotalOwners' => $countOwners,
                'countTotalShips' => $countOwned,
            ];
        }
        usort($res, static function (array $result1, array $result2): int {
            return $result2['countTotalShips'] - $result1['countTotalShips'];
        });

        return $this->json($res);
    }

    /**
     * @Route("/orga-fleets/{organization}/users/{shipName}", name="orga_fleet_users", methods={"GET"}, options={"expose":true})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function orgaFleetUsers(Request $request, string $organization, string $shipName): Response
    {
        $page = $request->query->get('page', 1);
        $itemsPerPage = 10;

        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/#/profile">profile page</a>.',
            ], 400);
        }
        if (!$citizen->hasOrganisation($organization)) {
            return $this->json([
                'error' => 'bad_organisation',
                'errorMessage' => sprintf('The organisation %s does not exist.', $organization),
            ], 404);
        }

        $citizens = $this->citizenRepository->getOwnersOfShip($organization, $this->shipInfosProvider->transformProviderToHangar($shipName), $page, $itemsPerPage);

        return $this->json($citizens, 200, [], ['groups' => 'orga_fleet']);
    }
}
