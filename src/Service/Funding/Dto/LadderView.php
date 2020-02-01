<?php

namespace App\Service\Funding\Dto;

class LadderView
{
    public int $rank;
    public int $amount = 0;
    public string $name;
    public ?string $mainOrgaName;
    public ?string $avatarUrl;
    public bool $me;

    public function __construct(
        int $rank,
        int $amount,
        string $name,
        ?string $mainOrgaName = null,
        ?string $avatarUrl = null,
        bool $me = false
    ) {
        $this->rank = $rank;
        $this->amount = $amount;
        $this->name = $name;
        $this->mainOrgaName = $mainOrgaName;
        $this->avatarUrl = $avatarUrl;
        $this->me = $me;
    }
}
