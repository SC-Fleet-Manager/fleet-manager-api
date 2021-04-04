<?php

namespace App\Infrastructure\Controller\Home;

use App\Application\Home\NumbersService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class NumbersController
{
    public function __construct(
        private NumbersService $numbersService,
        private SerializerInterface $serializer
    ) {
    }

    #[Route("/api/numbers", name: "spa_numbers", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        $output = $this->numbersService->handle();

        $json = $this->serializer->serialize($output, 'json');

        return (new JsonResponse($json, 200, [], true))->setSharedMaxAge(300);
    }
}
