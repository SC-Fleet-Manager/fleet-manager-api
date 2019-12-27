<?php

namespace App\Service\Funding\Dto;

class LadderView
{
    public int $rank;
    public int $amount = 0;
    public string $name;
    public bool $me;

    public function __construct(int $rank, int $amount, string $name, bool $me = false)
    {
        $this->rank = $rank;
        $this->amount = $amount;
        $this->name = $name;
        $this->me = $me;
    }
}
