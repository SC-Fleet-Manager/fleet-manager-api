<?php

namespace App\Infrastructure\Validator;

use App\Application\Repository\FleetRepositoryInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

class UniqueShipNameByUserValidator extends ConstraintValidator
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
        private Security $security,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, UniqueShipNameByUser::class);
        if ($value === null) {
            return;
        }
        Assert::string($value);

        /** @var User $user */
        $user = $this->security->getUser();
        Assert::notNull($user);

        $fleet = $this->fleetRepository->getFleetByUser($user->getId());
        if ($fleet === null) {
            // So, no ships : impossible to have a duplicate
            return;
        }

        $ship = $fleet->getShipByName($value);
        if ($ship !== null) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
