<?php

namespace App\Domain\Exception;

use App\Domain\OrgaId;
use Throwable;

class NotFoundOrganizationFleetException extends DomainException
{
    public static bool $notFound = true;

    public function __construct(
        OrgaId $orgaId,
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        $context['orgaId'] = $orgaId;
        parent::__construct(
            'not_found_organization_fleet',
            'This organization fleet does not exist.',
            $context,
            $message ?: sprintf('Unable to find organization fleet %s.', $orgaId),
            $code,
            $previous,
        );
    }
}
