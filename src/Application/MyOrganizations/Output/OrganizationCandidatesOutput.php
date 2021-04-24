<?php

namespace App\Application\MyOrganizations\Output;

class OrganizationCandidatesOutput
{
    public function __construct(
        /**
         * @var OrganizationCandidatesItemOutput[]
         */
        public array $candidates,
    ) {
    }
}
