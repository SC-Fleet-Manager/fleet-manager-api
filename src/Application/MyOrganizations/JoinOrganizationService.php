<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\AlreadyMemberOfOrganizationException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;

class JoinOrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private Clock $clock,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $memberId): void
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }

        if ($organization->isMemberOf($memberId)) {
            throw new AlreadyMemberOfOrganizationException($orgaId, $memberId);
        }

        $organization->addMember($memberId, false, $this->clock->now());

        $this->organizationRepository->save($organization);
    }
}
