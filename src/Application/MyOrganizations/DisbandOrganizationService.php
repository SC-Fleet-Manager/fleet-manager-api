<?php

namespace App\Application\MyOrganizations;

use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;

class DisbandOrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $founderId): void
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }
        if (!$organization->isFounder($founderId)) {
            throw new NotFounderOfOrganizationException($orgaId, $founderId);
        }

        $this->organizationRepository->deleteAll([$orgaId]);
        $this->organizationFleetRepository->deleteAll([$orgaId]);
    }
}
