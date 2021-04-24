<?php

namespace App\Domain\Exception;

use App\Domain\MemberId;
use App\Domain\OrgaId;
use Throwable;

class NotFounderOfOrganizationException extends DomainException
{
    public static bool $notFound = false;

    public function __construct(
        OrgaId $orgaId,
        MemberId $memberId,
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        $context['orgaId'] = $orgaId;
        $context['memberId'] = $memberId;
        parent::__construct(
            'not_founder_of_organization',
            'You are not the founder of this organization.',
            $context,
            $message ?: sprintf('User %s is not the founder of organization %s.', $memberId, $orgaId),
            $code,
            $previous,
        );
    }
}
