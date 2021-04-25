<?php

namespace App\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class UniqueUserHandle extends Constraint
{
    public string $fieldHandle;
    /** The user that should be ignored. */
    public ?string $fieldExcludeUserId;
    public string $message;

    public function __construct(string $fieldHandle, ?string $fieldExcludeUserId = null, ?string $message = null, $options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);
        $this->fieldHandle = $fieldHandle;
        $this->fieldExcludeUserId = $fieldExcludeUserId;
        $this->message = $message ?? 'This handle is already taken.';
    }

    public function getTargets(): string
    {
        return Constraint::CLASS_CONSTRAINT;
    }
}
