<?php

namespace App\Controller\Spa;

use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrganizationController extends AbstractController
{
    private OrganizationRepository $organizationRepository;

    public function __construct(OrganizationRepository $organizationRepository)
    {
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * @Route("/api/organization/{sid}", name="spa_organization", methods={"GET"})
     */
    public function __invoke(string $sid): Response
    {
        $orga = $this->organizationRepository->findOneBy(['organizationSid' => $sid]);
        if ($orga === null) {
            return $this->json([
                'error' => 'orga_not_exist',
                'errorMessage' => sprintf('The organization %s does not exist.', $sid),
            ], 404);
        }

        return $this->json($orga);
    }
}
