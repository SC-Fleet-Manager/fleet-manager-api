<?php

namespace App\Infrastructure\Validator;

use App\Domain\OrgaId;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniqueOrganizationSid extends Constraint
{
    /**
     * The orga that should be ignored.
     */
    public ?OrgaId $excludeOrgaId;
    public string $message;

    public function __construct(?OrgaId $excludeOrgaId = null, ?string $message = null, $options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);
        $this->excludeOrgaId = $excludeOrgaId;
        $this->message = $message ?? 'This SID is already taken.';
    }
}
