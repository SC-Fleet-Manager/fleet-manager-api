<?php

namespace App\Application\Provider;

use App\Domain\MemberId;
use App\Domain\MemberProfile;

interface MemberProfileProviderInterface
{
    /**
     * @param MemberId[] $memberIds
     *
     * @return MemberProfile[]
     */
    public function getProfiles(array $memberIds): array;
}
