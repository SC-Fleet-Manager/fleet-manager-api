<?php

namespace App\Application\MyOrganizations\Output;

use OpenApi\Annotations as OpenApi;

class OrganizationsCollectionOutput
{
    public function __construct(
        /**
         * @var OrganizationsItemOutput[]
         */
        public array $organizations,
        /**
         * @OpenApi\Property(type="string", format="url", nullable=true, example="https://api.fleet-manager.space/organizations?sinceId=00000000-0000-0000-0000-000000000001")
         */
        public ?string $nextUrl,
    ) {
    }
}
