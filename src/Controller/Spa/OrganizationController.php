<?php

namespace App\Controller\Spa;

use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class OrganizationController
{
    private $organizationRepository;
    private $serializer;

    public function __construct(OrganizationRepository $organizationRepository, SerializerInterface $serializer)
    {
        $this->organizationRepository = $organizationRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/organization/{sid}", name="spa_organization", methods={"GET"})
     */
    public function __invoke(string $sid): Response
    {
        $orga = $this->organizationRepository->findOneBy(['organizationSid' => $sid]);
        if ($orga === null) {
            return new JsonResponse($this->serializer->serialize([
                'error' => 'orga_not_exist',
                'errorMessage' => sprintf('The organization %s does not exist.', $sid),
            ], 'json'), 404, [], true);
        }

        return new JsonResponse($this->serializer->serialize($orga, 'json'), 200, [], true);
    }
}
