<?php

namespace App\Application\MyOrganizations;

use App\Application\MyOrganizations\Output\MyOrganizationsItemOutput;
use App\Application\MyOrganizations\Output\MyOrganizationsOutput;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\MemberId;
use App\Entity\Organization;

class MyOrganizationsService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function handle(MemberId $memberId): MyOrganizationsOutput
    {
        $organizations = $this->organizationRepository->getOrganizationByMember($memberId);

        return new MyOrganizationsOutput(
            array_map(static function (Organization $organization) use ($memberId): MyOrganizationsItemOutput {
                return new MyOrganizationsItemOutput(
                    $organization->getId(),
                    $organization->getName(),
                    $organization->getSid(),
                    $organization->getLogoUrl(),
                    $organization->hasJoined($memberId),
                );
            }, $organizations),
        );
    }
}
