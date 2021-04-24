<?php

namespace App\Application\MyOrganizations\Output;

class OrganizationMembersOutput
{
    public function __construct(
        /**
         * @var OrganizationMembersItemOutput[]
         */
        public array $members,
    ) {
    }
}
