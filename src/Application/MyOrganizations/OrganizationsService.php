<?php

namespace App\Application\MyOrganizations;

use App\Application\MyOrganizations\Output\OrganizationsCollectionOutput;
use App\Application\MyOrganizations\Output\OrganizationsItemOutput;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\OrgaId;
use App\Entity\Organization;

class OrganizationsService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function handle(string $baseUrl, int $itemsPerPage, ?OrgaId $sinceOrgaId = null, ?string $searchQuery = null): OrganizationsCollectionOutput
    {
        $organizations = $this->organizationRepository->getOrganizations($itemsPerPage, $sinceOrgaId, $searchQuery);

        return new OrganizationsCollectionOutput(
            array_map(static function (Organization $organization): OrganizationsItemOutput {
                return new OrganizationsItemOutput(
                    $organization->getId(),
                    $organization->getName(),
                    $organization->getSid(),
                    $organization->getLogoUrl(),
                );
            }, $organizations),
            count($organizations) === $itemsPerPage ? $baseUrl.'?sinceId='.end($organizations)->getId() : null,
        );
    }
}
