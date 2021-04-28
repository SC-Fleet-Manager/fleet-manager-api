<?php

namespace App\Infrastructure\Provider\Organizations;

use App\Application\Provider\MemberProfileProviderInterface;
use App\Domain\MemberProfile;

class InMemoryMemberProfileProvider implements MemberProfileProviderInterface
{
    /** @var MemberProfile[] */
    private array $memberProfiles = [];

    /**
     * @param MemberProfile[] $memberProfiles
     */
    public function setProfiles(array $memberProfiles): void
    {
        $this->memberProfiles = [];
        foreach ($memberProfiles as $memberProfile) {
            $this->memberProfiles[(string) $memberProfile->getId()] = $memberProfile;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getProfiles(array $memberIds): array
    {
        return $this->memberProfiles;
    }
}
