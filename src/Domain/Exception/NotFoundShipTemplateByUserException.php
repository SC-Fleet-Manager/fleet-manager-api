<?php

namespace App\Domain\Exception;

use App\Domain\ShipTemplateId;
use App\Domain\UserId;
use Throwable;

class NotFoundShipTemplateByUserException extends DomainException
{
    public static bool $notFound = true;

    public function __construct(
        UserId $userId,
        ShipTemplateId $templateId,
        array $context = [],
        $message = '',
        $code = 0,
        Throwable $previous = null,
    ) {
        $context['userId'] = $userId;
        $context['templateId'] = $templateId;
        parent::__construct(
            'not_found_user_ship_template',
            'This user has no this template.',
            $context,
            $message ?: sprintf('Unable to find ship template %s of user %s.', $templateId, $userId),
            $code,
            $previous,
        );
    }
}
