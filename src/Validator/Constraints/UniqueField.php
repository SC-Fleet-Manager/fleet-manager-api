<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Webmozart\Assert\Assert;

#[\Attribute]
class UniqueField extends Constraint
{
    public string $message;

    public function __construct(
        ?array $options = null,
        public string $entityClass = '',
        public string $field = 'email',
        ?string $message = null,
        array $groups = null,
        $payload = null
    ) {
        parent::__construct($options, $groups, $payload);
        Assert::classExists($this->entityClass);
        Assert::propertyExists($this->entityClass, $field);
        $this->message = $message ?: 'This email is taken. Please choose another.';
    }
}
