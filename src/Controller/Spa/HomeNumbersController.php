<?php

namespace App\Controller\Spa;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class HomeNumbersController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private Security $security
    ) {
    }

    #[Route("/api/numbers", name: "spa_numbers", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        $countUsers = $this->entityManager->getRepository(User::class)->countUsers();

        $json = $this->serializer->serialize([
            'users' => $countUsers,
        ], 'json');

        return (new JsonResponse($json, 200, [], true))->setSharedMaxAge(300);
    }

    #[Route("/api/toto")]
    public function toto(
        Request $request
    ) {
        if ($request->getRealMethod() === 'OPTIONS') {
            return new JsonResponse(null, 204);
        }
        dump($request->headers->get('authorization'));
        return new JsonResponse(
            $this->serializer->serialize(['user' => $this->security->getUser()], 'json', ['groups' => 'profile']),
            200, [], true
        );
    }
}
