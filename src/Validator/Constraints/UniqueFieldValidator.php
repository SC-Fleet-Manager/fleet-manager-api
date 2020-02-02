<?php

namespace App\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueFieldValidator extends ConstraintValidator
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function validate($value, Constraint $constraint): void
    {
        $repo = $this->entityManager->getRepository($constraint->entityClass);
        $foundEntity = $repo->findOneBy([$constraint->field => $value]);
        if ($foundEntity === null) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
