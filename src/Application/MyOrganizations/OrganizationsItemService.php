<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\MyOrganizations\Output\OrganizationsItemFleetOutput;
use App\Application\MyOrganizations\Output\OrganizationsItemWithFleetOutput;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\Exception\NotJoinedOrganizationMemberException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\OrganizationFleet;

class OrganizationsItemService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private Clock $clock,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $memberId): OrganizationsItemWithFleetOutput
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }

        if (!$organization->hasJoined($memberId)) {
            throw new NotJoinedOrganizationMemberException($orgaId, $memberId);
        }

        $fleet = $this->organizationFleetRepository->getOrganizationFleet($orgaId);
        if ($fleet === null) {
            $fleet = new OrganizationFleet($orgaId, $this->clock->now());
        }

        return new OrganizationsItemWithFleetOutput(
            $organization->getId(),
            $organization->getName(),
            $organization->getSid(),
            $organization->getLogoUrl(),
            $organization->isFounder($memberId),
            OrganizationsItemFleetOutput::createFromFleet($fleet),
        );
    }
}
