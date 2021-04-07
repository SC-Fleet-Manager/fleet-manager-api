<?php

namespace App\Infrastructure\Validator;

use App\Domain\Exception\FailedValidationException;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;
use Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiValidator implements ValidatorInterface
{
    public function __construct(
        private ValidatorInterface $decorated
    ) {
    }

    public function getMetadataFor($value): MetadataInterface
    {
        return $this->decorated->getMetadataFor($value);
    }

    public function hasMetadataFor($value): bool
    {
        return $this->decorated->hasMetadataFor($value);
    }

    public function validate($value, $constraints = null, $groups = null): ConstraintViolationListInterface
    {
        $violations = $this->decorated->validate($value, $constraints, $groups);
        $this->throwIfViolations($violations);

        return $violations;
    }

    public function validateProperty($object, string $propertyName, $groups = null): ConstraintViolationListInterface
    {
        $violations = $this->decorated->validateProperty($object, $propertyName, $groups);
        $this->throwIfViolations($violations);

        return $violations;
    }

    public function validatePropertyValue($objectOrClass, string $propertyName, $value, $groups = null): ConstraintViolationListInterface
    {
        $violations = $this->decorated->validatePropertyValue($objectOrClass, $propertyName, $value, $groups);
        $this->throwIfViolations($violations);

        return $violations;
    }

    public function startContext(): ContextualValidatorInterface
    {
        return $this->decorated->startContext();
    }

    public function inContext(ExecutionContextInterface $context): ContextualValidatorInterface
    {
        return $this->decorated->inContext($context);
    }

    private function throwIfViolations(ConstraintViolationListInterface $violations): void
    {
        if ($violations->count() > 0) {
            throw new FailedValidationException($violations);
        }
    }
}
