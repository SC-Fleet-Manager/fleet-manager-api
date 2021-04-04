<?php

namespace App\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class UniqueFieldValidator extends ConstraintValidator
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, UniqueField::class);
        /** @var UniqueField $constraint */
        $repo = $this->entityManager->getRepository($constraint->entityClass);
        $foundEntity = $repo->findOneBy([$constraint->field => $value]);
        if ($foundEntity === null) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
