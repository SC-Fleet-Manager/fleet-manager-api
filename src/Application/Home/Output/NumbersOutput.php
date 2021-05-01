<?php

namespace App\Application\Home\Output;

class NumbersOutput
{
    public function __construct(
        public int $users,
        public int $fleets,
        public int $ships,
    ) {
    }
}
