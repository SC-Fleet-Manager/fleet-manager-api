<?php

namespace App\Application\MyOrganizations\Output;

class OrganizationShipOwnersOutput
{
    public function __construct(
        /**
         * @var OrganizationShipOwnersItemOutput[]
         */
        public array $owners,
    ) {
    }
}
