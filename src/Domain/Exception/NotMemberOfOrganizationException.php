<?php

namespace App\Domain\Exception;

use App\Domain\MemberId;
use App\Domain\OrgaId;
use Throwable;

class NotMemberOfOrganizationException extends DomainException
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
            'not_member_of_organization',
            $userMessage ?: 'You have not joined this organization yet.',
            $context,
            $message ?: sprintf('The user %s cannot unjoin the organization %s : he is not a member.', $memberId, $orgaId),
            $code,
            $previous,
        );
    }
}
