<?php

namespace App\Application\Provider;

use App\Domain\MemberId;
use App\Domain\MemberProfile;

interface MemberProfileProviderInterface
{
    /**
     * @param MemberId[] $candidateIds
     *
     * @return MemberProfile[]
     */
    public function getProfiles(array $candidateIds): array;
}
