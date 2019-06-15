<?php

namespace App\Controller;

use App\Entity\Citizen;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\UserRepository;
use App\Service\ShipInfosProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api", name="fleet_")
 */
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
     * @Route("/fleet/my-fleet", name="my_fleet", methods={"GET"}, options={"expose":true})
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
        $fleet = $citizen->getLastFleet();
        $shipInfos = $this->shipInfosProvider->getAllShips();

        return $this->json([
            'fleet' => $fleet,
            'shipInfos' => $shipInfos,
        ], 200, [], ['groups' => ['my-fleet']]);
    }

    /**
     * @Route("/fleet/user-fleet/{handle}", name="user_fleet", methods={"GET"}, options={"expose":true})
     */
    public function userFleet(string $handle): Response
    {
        /** @var Citizen|null $citizen */
        $citizen = $this->citizenRepository->findOneBy(['actualHandle' => $handle]);
        if ($citizen === null) {
            return $this->json([
                'error' => 'citizen_not_found',
                'errorMessage' => sprintf('The citizen %s does not exist.', $handle),
            ], 404);
        }

        /** @var User|null $user */
        $user = $this->userRepository->findOneBy(['citizen' => $citizen]);
        if ($user === null) {
            return $this->json([
                'error' => 'user_not_found',
                'errorMessage' => sprintf('The user of citizen %s does not exist.', $handle),
            ], 404);
        }

        if (!$this->isGranted('ACCESS_USER_FLEET', $user)) {
            return $this->json([
                'error' => 'no_rights',
                'errorMessage' => 'You have no rights to see this fleet.',
            ], 403);
        }

        $fleet = $citizen->getLastFleet();
        $shipInfos = $this->shipInfosProvider->getAllShips();

        return $this->json([
            'fleet' => $fleet,
            'shipInfos' => $shipInfos,
        ], 200, [], ['groups' => ['public-fleet']]);
    }
}
