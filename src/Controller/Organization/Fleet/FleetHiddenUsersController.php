<?php

namespace App\Controller\Organization\Fleet;

use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class FleetHiddenUsersController extends AbstractController
{
    private $security;
    private $fleetOrganizationGuard;
    private $shipInfosProvider;
    private $logger;
    private $citizenRepository;

    public function __construct(
        Security $security,
        FleetOrganizationGuard $fleetOrganizationGuard,
        ShipInfosProviderInterface $shipInfosProvider,
        LoggerInterface $logger,
        CitizenRepository $citizenRepository
    ) {
        $this->security = $security;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->shipInfosProvider = $shipInfosProvider;
        $this->logger = $logger;
        $this->citizenRepository = $citizenRepository;
    }

    public function __invoke(string $organizationSid, string $providerShipName): Response
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

        $shipName = $this->shipInfosProvider->transformProviderToHangar($providerShipName);
        $shipInfo = $this->shipInfosProvider->getShipByName($providerShipName);
        if ($shipInfo === null) {
            $this->logger->warning('Ship not found in the ship infos provider.', ['hangarShipName' => $providerShipName, 'provider' => get_class($this->shipInfosProvider)]);

            return $this->json([]);
        }

        $loggedCitizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $loggedCitizen = $this->getUser()->getCitizen();
        }

        $totalHiddenOwners = $this->citizenRepository->countHiddenOwnersOfShip($organizationSid, $shipName, $loggedCitizen);

        return $this->json([
            'hiddenUsers' => $totalHiddenOwners,
        ]);
    }
}
