<?php

namespace App\Service\Organization;

use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Service\Dto\ShipFamilyFilter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

class ShipFamilyFilterFactory
{
    private Security $security;
    private CitizenRepository $citizenRepository;

    public function __construct(Security $security, CitizenRepository $citizenRepository)
    {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
    }

    public function create(Request $request, string $organizationSid): ShipFamilyFilter
    {
        $filters = $request->query->get('filters', []);

        $shipFamilyFilter = new ShipFamilyFilter(
            $filters['shipGalaxyIds'] ?? [],
            $filters['citizenIds'] ?? [],
            $filters['shipSizes'] ?? [],
            $filters['shipStatus'] ?? null
        );

        // remove non visibles filters (e.g., private citizens, etc.)
        $loggedCitizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $loggedCitizen = $user->getCitizen();
        }

        // orga public + not in this orga ? we can't filter by citizens
        if ($loggedCitizen !== null) {
            if (!$loggedCitizen->hasOrganization($organizationSid)) {
                $shipFamilyFilter->citizenIds = [];
            }
        } else {
            $shipFamilyFilter->citizenIds = [];
        }

        $visibleCitizens = $this->citizenRepository->findVisiblesByOrganization($organizationSid, $loggedCitizen);
        $shipFamilyFilter->citizenIds = array_filter($shipFamilyFilter->citizenIds, static function (string $citizenId) use (&$visibleCitizens): bool {
            foreach ($visibleCitizens as $visibleCitizen) {
                if ($citizenId === $visibleCitizen->getId()->toString()) {
                    return true;
                }
            }

            return false;
        });

        return $shipFamilyFilter;
    }
}
