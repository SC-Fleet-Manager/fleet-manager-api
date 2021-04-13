<?php

namespace App\Infrastructure\Validator;

use App\Application\Repository\OrganizationRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class UniqueOrganizationSidValidator extends ConstraintValidator
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        /** @var UniqueOrganizationSid $constraint */
        Assert::isInstanceOf($constraint, UniqueOrganizationSid::class);
        if ($value === null) {
            return;
        }
        Assert::string($value);

        $orga = $this->organizationRepository->getOrganizationBySid($value);
        if ($orga === null) {
            return;
        }
        if ($constraint->excludeOrgaId !== null && $orga->getId()->equals($constraint->excludeOrgaId)) {
            // ignore if it's the excluded orgaId
            return;
        }
        $this->context->buildViolation($constraint->message)->addViolation();
    }
}
