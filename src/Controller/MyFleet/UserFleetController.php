<?php

namespace App\Controller\MyFleet;

use App\Entity\Citizen;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\UserRepository;
use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserFleetController extends AbstractController
{
    private $citizenRepository;
    private $userRepository;
    private $shipInfosProvider;

    public function __construct(
        CitizenRepository $citizenRepository,
        UserRepository $userRepository,
        ShipInfosProviderInterface $shipInfosProvider
    ) {
        $this->citizenRepository = $citizenRepository;
        $this->userRepository = $userRepository;
        $this->shipInfosProvider = $shipInfosProvider;
    }

    /**
     * @Route("/api/fleet/user-fleet/{handle}", name="my_fleet_user_fleet", methods={"GET"})
     */
    public function __invoke(string $handle): Response
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
