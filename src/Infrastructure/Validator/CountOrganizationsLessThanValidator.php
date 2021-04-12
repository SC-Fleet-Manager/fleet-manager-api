<?php

namespace App\Infrastructure\Validator;

use App\Application\Repository\OrganizationRepositoryInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class CountOrganizationsLessThanValidator extends ConstraintValidator
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private Security $security,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        /** @var CountOrganizationsLessThan $constraint */
        Assert::isInstanceOf($constraint, CountOrganizationsLessThan::class);
        if ($value === null) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        Assert::notNull($user);

        $orgas = $this->organizationRepository->getOrganizationsOfFounder($user->getId());
        if (count($orgas) >= $constraint->max) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
