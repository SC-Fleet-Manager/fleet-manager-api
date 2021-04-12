<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\OrgaId;
use App\Domain\UserId;
use App\Entity\Organization;

class CreateOrganizationService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private OrganizationRepositoryInterface $organizationRepository,
        private Clock $clock,
    ) {
    }

    public function handle(OrgaId $orgaId, UserId $userId, string $name, string $sid, ?string $logoUrl = null): void
    {
        $orga = new Organization($orgaId, $userId, $name, $sid, $logoUrl, $this->clock->now());

        $this->organizationRepository->save($orga);
    }
}
