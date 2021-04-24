<?php

namespace App\Domain\Exception;

use App\Domain\MemberId;
use App\Domain\OrgaId;
use Throwable;

class FounderOfOrganizationException extends DomainException
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
            'founder_of_organization',
            $userMessage ?: 'You are the founder of this organization.',
            $context,
            $message ?: sprintf('User %s is the founder of organization %s.', $memberId, $orgaId),
            $code,
            $previous,
        );
    }
}
