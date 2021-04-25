<?php

namespace App\Infrastructure\Provider\Organizations;

use App\Application\Profile\PublicProfilesService;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Domain\MemberId;
use App\Domain\MemberProfile;

class DirectCallMemberProfileProvider implements MemberProfileProviderInterface
{
    public function __construct(
        private PublicProfilesService $profilesService,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getProfiles(array $candidateIds): array
    {
        $profiles = $this->profilesService->handle($candidateIds);

        $result = [];
        foreach ($profiles as $profile) {
            $result[] = new MemberProfile(
                MemberId::fromString((string) $profile->id),
                $profile->nickname,
            );
        }

        return $result;
    }
}
