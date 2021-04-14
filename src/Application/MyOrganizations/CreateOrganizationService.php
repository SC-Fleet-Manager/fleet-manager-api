<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;

class CreateOrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private Clock $clock,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $memberId, string $name, string $sid, ?string $logoUrl = null): void
    {
        $orga = new Organization($orgaId, $memberId, $name, $sid, $logoUrl, $this->clock->now());

        $this->organizationRepository->save($orga);
    }
}
