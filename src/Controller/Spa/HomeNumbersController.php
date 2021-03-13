<?php

namespace App\Controller\Spa;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class HomeNumbersController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer
    ) {
    }

    #[Route("/api/numbers", name: "spa_numbers", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        $countUsers = count($this->entityManager->getRepository(User::class)->findAll());

        $json = $this->serializer->serialize([
            'users' => $countUsers,
        ], 'json');

        return (new JsonResponse($json, 200, [], true))->setSharedMaxAge(300);
    }
}
