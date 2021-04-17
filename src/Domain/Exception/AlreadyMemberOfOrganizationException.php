<?php

namespace App\Domain\Exception;

use App\Domain\MemberId;
use App\Domain\OrgaId;
use Throwable;

class AlreadyMemberOfOrganizationException extends DomainException
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
            'already_member_of_organization',
            $userMessage ?: 'You are already member of this organization.',
            $context,
            $message ?: sprintf('The user %s is already member of organization %s', $memberId, $orgaId),
            $code,
            $previous,
        );
    }
}
