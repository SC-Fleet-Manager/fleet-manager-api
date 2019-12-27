<?php

namespace App\Controller\Profile;

use App\Entity\Citizen;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicProfileController extends AbstractController
{
    private CitizenRepository $citizenRepository;
    private UserRepository $userRepository;

    public function __construct(CitizenRepository $citizenRepository, UserRepository $userRepository)
    {
        $this->citizenRepository = $citizenRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * Retrieves profile infos : user properties.
     *
     * @Route("/api/public-profile/{handle}", name="public_profile", methods={"GET"})
     */
    public function __invoke(Request $request, string $handle): Response
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
                'errorMessage' => 'You have no rights to see this profile.',
            ], 403);
        }

        return $this->json($user, 200, [], ['groups' => ['public_profile']]);
    }
}
