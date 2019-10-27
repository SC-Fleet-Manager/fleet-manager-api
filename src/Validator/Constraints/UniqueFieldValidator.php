<?php

namespace App\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueFieldValidator extends ConstraintValidator
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param UniqueField $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        $userRepo = $this->entityManager->getRepository($constraint->entityClass);
        /** @var UserInterface $foundUser */
        $foundUser = $userRepo->findOneBy([$constraint->field => $value]);
        if ($foundUser === null) {
            return;
        }

        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
