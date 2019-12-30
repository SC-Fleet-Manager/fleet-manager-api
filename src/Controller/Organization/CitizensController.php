<?php

namespace App\Controller\Organization;

use App\Entity\Citizen;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Service\Organization\Fleet\FleetOrganizationGuard;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CitizensController extends AbstractController
{
    private Security $security;
    private FleetOrganizationGuard $fleetOrganizationGuard;
    private CitizenRepository $citizenRepository;

    public function __construct(
        Security $security,
        FleetOrganizationGuard $fleetOrganizationGuard,
        CitizenRepository $citizenRepository
    ) {
        $this->security = $security;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
        $this->citizenRepository = $citizenRepository;
    }

    /**
     * @Route("/api/organization/{organizationSid}/citizens", name="organization_citizens", methods={"GET"})
     */
    public function __invoke(string $organizationSid): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        $citizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $citizen = $user->getCitizen();
        }

        // orga public (not auth) OR not in this orga ? we can't filter by citizens
        if ($citizen === null || !$citizen->hasOrganization($organizationSid)) {
            return $this->json([]);
        }

        $citizens = $this->citizenRepository->findVisiblesByOrganization($organizationSid, $citizen);

        // format for vue-select data
        $res = array_map(static function (Citizen $citizen) {
            return [
                'id' => $citizen->getId(),
                'label' => $citizen->getActualHandle()->getHandle(),
            ];
        }, $citizens);

        $collator = \Collator::create(\Locale::getDefault());
        usort($res, static function (array $item1, array $item2) use ($collator): int {
            return $collator->compare($item1['label'], $item2['label']);
        });

        return $this->json($res);
    }
}
