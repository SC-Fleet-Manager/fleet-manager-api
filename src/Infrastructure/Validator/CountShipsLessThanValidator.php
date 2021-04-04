<?php

namespace App\Infrastructure\Validator;

use App\Application\Repository\FleetRepositoryInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class CountShipsLessThanValidator extends ConstraintValidator
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private Security $security,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, CountShipsLessThan::class);
        if ($value === null) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();
        Assert::notNull($user);

        $fleet = $this->fleetRepository->getFleetByUser($user->getId());
        if ($fleet === null) {
            if ($constraint->max === 0) {
                $this->context->buildViolation($constraint->message)->addViolation();
            }

            return;
        }

        if (count($fleet->getShips()) >= $constraint->max) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
