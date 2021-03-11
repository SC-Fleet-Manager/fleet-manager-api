<?php

namespace App\Controller\Spa;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class MeController extends AbstractController
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/api/me", name="me", methods={"GET","OPTIONS"})
     */
    public function __invoke(Request $request): Response
    {
        if ($request->isMethod('OPTIONS')) {
            // Preflight CORS request.
            return new JsonResponse(null, 204, [
                'Access-Control-Allow-Headers' => 'Content-Type, X-FME-Version',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
            ]);
        }

        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();

        return $this->json($user, 200, [], ['groups' => 'me:read']);
    }
}
