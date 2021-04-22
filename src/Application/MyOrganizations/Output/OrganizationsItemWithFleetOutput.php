<?php

namespace App\Application\MyOrganizations\Output;

use App\Domain\OrgaId;
use OpenApi\Annotations as OpenApi;

class OrganizationsItemWithFleetOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", format="uid", example="00000000-0000-0000-0000-000000000001")
         */
        public OrgaId $id,
        /**
         * @OpenApi\Property(type="string", example="Force Coloniale Unifiée")
         */
        public string $name,
        /**
         * @OpenApi\Property(type="string", example="fcu")
         */
        public string $sid,
        /**
         * @OpenApi\Property(type="string", format="url", nullable=true, example="https://robertsspaceindustries.com/media/p7en31fqpos97r/logo/FCU-Logo.png")
         */
        public ?string $logoUrl,
        /**
         * @OpenApi\Property(type="boolean", description="true if logged user is founder of the organization.")
         */
        public bool $founder,
        public OrganizationsItemFleetOutput $fleet,
    ) {
    }
}
