<?php

namespace App\Controller\Spa;

use App\Entity\Fleet;
use App\Entity\Organization;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class HomeNumbersController
{
    private EntityManagerInterface $entityManager;
    private SerializerInterface $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/numbers", name="spa_numbers", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $countOrgas = count($this->entityManager->getRepository(Organization::class)->findAll());
        $countUsers = count($this->entityManager->getRepository(User::class)->findAll());
        $countShips = $this->entityManager->getRepository(Fleet::class)->countTotalShips();

        $json = $this->serializer->serialize([
            'organizations' => $countOrgas,
            'users' => $countUsers,
            'ships' => $countShips,
        ], 'json');

        return (new JsonResponse($json, 200, [], true))->setSharedMaxAge(300);
    }
}
