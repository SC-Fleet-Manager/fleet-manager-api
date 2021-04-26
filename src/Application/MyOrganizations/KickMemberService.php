<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\FounderOfOrganizationException;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\Exception\NotFoundOrganizationFleetException;
use App\Domain\Exception\NotJoinedOrganizationMemberException;
use App\Domain\MemberId;
use App\Domain\OrgaId;

class KickMemberService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private Clock $clock,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $founderId, MemberId $memberId): void
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }
        if (!$organization->isFounder($founderId)) {
            throw new NotFounderOfOrganizationException($orgaId, $founderId);
        }
        if ($organization->isFounder($memberId)) {
            throw new FounderOfOrganizationException($orgaId, $memberId, userMessage: 'You cannot kick yourself out of your organization but you can disband it.');
        }
        if (!$organization->hasJoined($memberId)) {
            throw new NotJoinedOrganizationMemberException($orgaId, $memberId);
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
