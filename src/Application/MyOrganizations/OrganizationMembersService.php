<?php

namespace App\Application\MyOrganizations;

use App\Application\MyOrganizations\Output\OrganizationMembersItemOutput;
use App\Application\MyOrganizations\Output\OrganizationMembersOutput;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\MemberId;
use App\Domain\MemberProfile;
use App\Domain\OrgaId;

class OrganizationMembersService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private MemberProfileProviderInterface $memberProfileProvider,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $founderId): OrganizationMembersOutput
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }

        if (!$organization->isFounder($founderId)) {
            throw new NotFounderOfOrganizationException($orgaId, $founderId);
        }

        $joinedMembers = $organization->getJoinedMembers($this->memberProfileProvider);

        return new OrganizationMembersOutput(
            array_map(static function (MemberProfile $memberProfile): OrganizationMembersItemOutput {
                return new OrganizationMembersItemOutput(
                    $memberProfile->getId(),
                    $memberProfile->getNickname(),
                );
            }, $joinedMembers),
        );
    }
}
