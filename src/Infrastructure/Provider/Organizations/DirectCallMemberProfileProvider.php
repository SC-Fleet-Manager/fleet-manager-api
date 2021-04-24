<?php

namespace App\Infrastructure\Provider\Organizations;

use App\Application\Profile\ProfilesService;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Domain\MemberId;
use App\Domain\MemberProfile;

class DirectCallMemberProfileProvider implements MemberProfileProviderInterface
{
    public function __construct(
        private ProfilesService $profilesService,
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
