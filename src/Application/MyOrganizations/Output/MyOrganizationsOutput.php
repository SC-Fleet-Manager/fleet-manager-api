<?php

namespace App\Application\MyOrganizations\Output;

class MyOrganizationsOutput
{
    public function __construct(
        /**
         * @var MyOrganizationsItemOutput[]
         */
        public array $organizations,
    ) {
    }
}
