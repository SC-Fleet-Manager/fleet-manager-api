<?php

namespace App\Application\MyOrganizations\Output;

use App\Domain\MemberId;
use OpenApi\Annotations as OpenApi;

class OrganizationMembersItemOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", format="uid", example="00000000-0000-0000-0000-000000000001")
         */
        public MemberId $id,
        /**
         * @OpenApi\Property(type="string", example="Ioni")
         */
        public ?string $nickname,
        /**
         * @OpenApi\Property(type="string", example="ioni14")
         */
        public ?string $handle,
    ) {
    }
}
