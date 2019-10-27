<?php

namespace App\Controller\Organization\Fleet;

use App\Domain\SpectrumIdentification;
use App\Entity\User;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Organization\Fleet\OrganizationFleetGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CreateFleetFileController extends AbstractController
{
    private $security;
    private $fleetOrganizationGuard;
    private $organizationFleetGenerator;

    public function __construct(Security $security, FleetOrganizationGuard $fleetOrganizationGuard, OrganizationFleetGenerator $organizationFleetGenerator)
    {
        $this->security = $security;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->organizationFleetGenerator = $organizationFleetGenerator;
    }

    /**
     * Combines all last version fleets of all citizen members of a specific organization.
     * Returns a downloadable json file.
     *
     * @Route("/api/create-organization-fleet-file/{organization}", name="orga_fleet_create_fleet_file", methods={"GET"})
     */
    public function __invoke(string $organization): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organization)) {
            return $response;
        }

        // If viewer is not in this orga, he doesn't see the users
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return new JsonResponse([
                'error' => 'no_citizen_created',
            ], 400);
        }
        if (!$citizen->hasOrganization($organization)) {
            return new JsonResponse([
                'error' => 'not_in_orga',
            ], 404);
        }

        $file = $this->organizationFleetGenerator->generateFleetFile(new SpectrumIdentification($organization));

        $fileResponse = $this->file($file, 'organization_fleet.json');
        $fileResponse->headers->set('Content-Type', 'application/json');
        $fileResponse->deleteFileAfterSend();

        return $fileResponse;
    }
}
