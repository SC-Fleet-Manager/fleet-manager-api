<?php

namespace App\Controller\Spa;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MeController extends AbstractController
{
    public function __construct(
        private Security $security
    ) {
    }

    #[Route("/api/me", name: "me", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();

        return $this->json($user, 200, [], ['groups' => 'me:read']);
    }
}
