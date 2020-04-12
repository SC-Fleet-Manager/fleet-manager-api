<?php

namespace App\Controller\Organization\Fleet;

use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Organization\ShipFamilyFilterFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class FleetHiddenUsersController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private Security $security;
    private FleetOrganizationGuard $fleetOrganizationGuard;
    private CitizenRepository $citizenRepository;
    private ShipFamilyFilterFactory $shipFamilyFilterFactory;

    public function __construct(
        Security $security,
        FleetOrganizationGuard $fleetOrganizationGuard,
        CitizenRepository $citizenRepository,
        ShipFamilyFilterFactory $shipFamilyFilterFactory
    ) {
        $this->security = $security;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->citizenRepository = $citizenRepository;
        $this->shipFamilyFilterFactory = $shipFamilyFilterFactory;
    }

    public function __invoke(Request $request, string $organizationSid, string $providerShipId): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        // If viewer is not in this orga, he doesn't see the users
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
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
                return new JsonResponse([
                    'hiddenUsers' => 0,
                ]);
            }
        } else {
            return new JsonResponse([
                'hiddenUsers' => 0,
            ]);
        }

        $loggedCitizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $loggedCitizen = $this->getUser()->getCitizen();
        }

        $shipFamilyFilter = $this->shipFamilyFilterFactory->create($request, $organizationSid);

        $totalHiddenOwners = $this->citizenRepository->countHiddenOwnersOfShip($organizationSid, Uuid::fromString($providerShipId), $loggedCitizen);

        return $this->json([
            'hiddenUsers' => $totalHiddenOwners,
            'citizenFiltered' => $shipFamilyFilter->citizenIds !== [],
        ]);
    }
}
