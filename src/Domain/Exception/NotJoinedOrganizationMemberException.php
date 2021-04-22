<?php

namespace App\Domain\Exception;

use App\Domain\MemberId;
use App\Domain\OrgaId;
use Throwable;

class NotJoinedOrganizationMemberException extends DomainException
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
        $context['userId'] = $memberId;
        parent::__construct(
            'not_joined_organization_member',
            $userMessage ?: 'You are not a member of this organization.',
            $context,
            $message ?: sprintf('User %s not full joined the organization %s.', $memberId, $orgaId),
            $code,
            $previous,
        );
    }
}
