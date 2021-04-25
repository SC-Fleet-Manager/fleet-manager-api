<?php

namespace App\Application\MyOrganizations;

use App\Application\MyOrganizations\Output\OrganizationCandidatesItemOutput;
use App\Application\MyOrganizations\Output\OrganizationCandidatesOutput;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\MemberId;
use App\Domain\MemberProfile;
use App\Domain\OrgaId;

class OrganizationCandidatesService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private MemberProfileProviderInterface $memberProfileProvider,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $founderId): OrganizationCandidatesOutput
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }

        if (!$organization->isFounder($founderId)) {
            throw new NotFounderOfOrganizationException($orgaId, $founderId);
        }

        $candidates = $organization->getCandidates($this->memberProfileProvider);

        return new OrganizationCandidatesOutput(
            array_map(static function (MemberProfile $memberProfile): OrganizationCandidatesItemOutput {
                return new OrganizationCandidatesItemOutput(
                    $memberProfile->getId(),
                    $memberProfile->getNickname(),
                    $memberProfile->getHandle(),
                );
            }, $candidates),
        );
    }
}
