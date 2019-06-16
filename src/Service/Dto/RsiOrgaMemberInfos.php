<?php

namespace App\Service\Dto;

class RsiOrgaMemberInfos
{
    public const STATUS_REGISTERED = 'registered';
    public const STATUS_FLEET_UPLOADED = 'fleet_uploaded';
    public const STATUS_NOT_REGISTERED = 'not_registered';

    public $handle;
    public $nickname;
    public $avatarUrl;
    public $rank;
    public $rankName;

    public function __construct(string $handle, string $nickname, ?string $avatarUrl, int $rank, string $rankName)
    {
        $this->handle = $handle;
        $this->nickname = $nickname;
        $this->avatarUrl = $avatarUrl;
        $this->rank = $rank;
        $this->rankName = $rankName;
    }
}
