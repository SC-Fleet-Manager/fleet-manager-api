<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\FounderOfOrganizationException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\Exception\NotFoundOrganizationFleetException;
use App\Domain\Exception\NotMemberOfOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;

class LeaveOrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private Clock $clock,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $memberId): void
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }

        if ($organization->isFounder($memberId)) {
            throw new FounderOfOrganizationException($orgaId, $memberId, userMessage: 'You cannot leave your own organization but you can disband it.');
        }

        if (!$organization->isMemberOf($memberId)) {
            throw new NotMemberOfOrganizationException($orgaId, $memberId);
        }

        $organization->unjoinMember($memberId, $this->clock->now());
        $this->organizationRepository->save($organization);

        $fleet = $this->organizationFleetRepository->getOrganizationFleet($orgaId);
        if ($fleet === null) {
            throw new NotFoundOrganizationFleetException($orgaId);
        }
        $fleet->deleteShipsOfMember($memberId, $this->clock->now());
        $this->organizationFleetRepository->saveAll([$fleet]);
    }
}
