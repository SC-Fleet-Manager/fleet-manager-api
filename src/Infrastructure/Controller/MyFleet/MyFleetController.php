<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\Home\MeService;
use App\Application\MyFleet\MyFleetService;
use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class MyFleetController
{
    public function __construct(
        private MyFleetService $myFleetService,
        private Security $security,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route("/api/my-fleet", name: "my_fleet", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->myFleetService->handle($user->getId());

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
