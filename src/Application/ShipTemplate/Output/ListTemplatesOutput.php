<?php

namespace App\Application\ShipTemplate\Output;

class ListTemplatesOutput
{
    public function __construct(
        /**
         * @var ListTemplatesItemOutput[]
         */
        public array $items = [],
    ) {
    }
}
