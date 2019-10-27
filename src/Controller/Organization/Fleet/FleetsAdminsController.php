<?php

namespace App\Controller\Organization\Fleet;

use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class FleetsAdminsController extends AbstractController
{
    private $security;
    private $fleetOrganizationGuard;
    private $citizenRepository;

    public function __construct(Security $security, FleetOrganizationGuard $fleetOrganizationGuard, CitizenRepository $citizenRepository)
    {
        $this->security = $security;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->citizenRepository = $citizenRepository;
    }

    public function __invoke(string $organizationSid): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        // If viewer is not in this orga, he doesn't see the users
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return new JsonResponse([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }
        if (!$citizen->hasOrganization($organizationSid)) {
            return new JsonResponse([]);
        }

        $admins = $this->citizenRepository->findAdminByOrganization($organizationSid);

        return $this->json($admins, 200, [], ['groups' => 'orga_fleet_admin']);
    }
}
