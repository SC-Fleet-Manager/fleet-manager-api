<?php

namespace App\Domain\Exception;

use App\Domain\MemberId;
use App\Domain\OrgaId;
use Throwable;

class FullyJoinedMemberOfOrganizationException extends DomainException
{
    public static bool $notFound = false;

    public function __construct(
        OrgaId $orgaId,
        MemberId $memberId,
        string $userMessage = '',
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        $context['orgaId'] = $orgaId;
        $context['memberId'] = $memberId;
        parent::__construct(
            'fully_joined_member_of_organization',
            $userMessage ?: 'You are member of this organization. You can leave it on its dashboard page.',
            $context,
            $message ?: sprintf('The user %s is fully member of organization %s', $memberId, $orgaId),
            $code,
            $previous,
        );
    }
}
