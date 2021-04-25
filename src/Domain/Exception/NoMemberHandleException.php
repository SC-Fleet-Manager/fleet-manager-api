<?php

namespace App\Domain\Exception;

use App\Domain\MemberId;
use Throwable;

class NoMemberHandleException extends DomainException
{
    public static bool $notFound = false;

    public function __construct(
        MemberId $memberId,
        string $userMessage = '',
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        $context['memberId'] = $memberId;
        parent::__construct(
            'no_member_handle',
            $userMessage ?: 'You must create your handle first in Profile page.',
            $context,
            $message ?: sprintf('The user %s has no handle.', $memberId),
            $code,
            $previous,
        );
    }
}
