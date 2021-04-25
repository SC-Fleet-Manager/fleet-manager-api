<?php

namespace App\Infrastructure\Provider\Organizations;

use App\Application\Provider\MemberProfileProviderInterface;
use App\Domain\MemberProfile;

class InMemoryMemberProfileProvider implements MemberProfileProviderInterface
{
    /** @var MemberProfile[] */
    private array $memberProfiles = [];

    public function setProfiles(array $memberProfiles): void
    {
        $this->memberProfiles = $memberProfiles;
    }

    /**
     * {@inheritDoc}
     */
    public function getProfiles(array $memberIds): array
    {
        return $this->memberProfiles;
    }
}
