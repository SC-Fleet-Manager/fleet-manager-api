<?php

namespace App\Infrastructure\Validator;

use Symfony\Component\Validator\Constraint;
use Webmozart\Assert\Assert;

#[\Attribute(\Attribute::TARGET_CLASS)]
class CountOrganizationsLessThan extends Constraint
{
    public string $message;
    public int $max;

    public function __construct(int $max, ?string $message = null, $options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);
        Assert::greaterThanEq($max, 0);
        $this->message = $message ?? "You have reached the limit of $max organizations created.";
        $this->max = $max;
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
