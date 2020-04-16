<?php

namespace App\Controller\Organization\Fleet;

use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use App\Service\Organization\ShipFamilyFilterFactory;
use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class FleetUsersController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private Security $security;
    private FleetOrganizationGuard $fleetOrganizationGuard;
    private ShipInfosProviderInterface $shipInfosProvider;
    private CitizenRepository $citizenRepository;
    private ShipFamilyFilterFactory $shipFamilyFilterFactory;

    public function __construct(
        Security $security,
        FleetOrganizationGuard $fleetOrganizationGuard,
        ShipInfosProviderInterface $shipInfosProvider,
        CitizenRepository $citizenRepository,
        ShipFamilyFilterFactory $shipFamilyFilterFactory
    ) {
        $this->security = $security;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->shipInfosProvider = $shipInfosProvider;
        $this->citizenRepository = $citizenRepository;
        $this->shipFamilyFilterFactory = $shipFamilyFilterFactory;
    }

    public function __invoke(Request $request, string $organizationSid, string $providerShipId): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        $page = $request->query->getInt('page', 1);
        $itemsPerPage = 10;

        $defaultResponse = new JsonResponse([
            'users' => [],
            'page' => 1,
            'lastPage' => 1,
            'total' => 0,
        ]);

        if (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $defaultResponse;
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
            return $defaultResponse;
        }

        $shipFamilyFilter = $this->shipFamilyFilterFactory->create($request, $organizationSid);

        $shipInfo = $this->shipInfosProvider->getShipById(Uuid::fromString($providerShipId));

        // filtering
        if (count($shipFamilyFilter->shipSizes) > 0 && !in_array($shipInfo->size, $shipFamilyFilter->shipSizes, false)) {
            return $defaultResponse;
        }
        if ($shipFamilyFilter->shipStatus !== null && $shipFamilyFilter->shipStatus !== $shipInfo->productionStatus) {
            return $defaultResponse;
        }
        if ($shipFamilyFilter->shipGalaxyIds !== [] && !in_array($providerShipId, $shipFamilyFilter->shipGalaxyIds, true)) {
            return $defaultResponse;
        }

        $loggedCitizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $loggedCitizen = $this->getUser()->getCitizen();
        }

        $countOwners = $this->citizenRepository->countOwnersOfShip(
            $organizationSid,
            Uuid::fromString($providerShipId),
            $loggedCitizen,
            $shipFamilyFilter);

        $users = $this->citizenRepository->getOwnersOfShip(
            $organizationSid,
            Uuid::fromString($providerShipId),
            $loggedCitizen,
            $shipFamilyFilter,
            $page,
            $itemsPerPage);

        $lastPage = (int) ceil($countOwners / $itemsPerPage);

        return $this->json([
            'users' => $users,
            'page' => $page,
            'lastPage' => $lastPage > 0 ? $lastPage : 1,
            'total' => $countOwners,
        ], 200, [], ['groups' => 'orga_fleet']);
    }
}
