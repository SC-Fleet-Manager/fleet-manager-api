<?php

namespace App\Infrastructure\Validator;

use App\Application\Repository\UserRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class UniqueUserHandleValidator extends ConstraintValidator
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        /** @var UniqueUserHandle $constraint */
        Assert::isInstanceOf($constraint, UniqueUserHandle::class);
        if ($value === null) {
            return;
        }
        Assert::object($value);

        $handle = $value->{$constraint->fieldHandle};

        $user = $this->userRepository->getByHandle($handle);
        if ($user === null) {
            return;
        }

        $userId = $value->{$constraint->fieldExcludeUserId} ?? null;
        if ($constraint->fieldExcludeUserId !== null && $user->getId()->equals($userId)) {
            // ignore if it's the excluded ID
            return;
        }
        $this->context->buildViolation($constraint->message)->atPath($constraint->fieldHandle)->addViolation();
    }
}
