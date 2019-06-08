<?php

namespace App\Security\Voter;

use App\Entity\Citizen;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AccessibleUserFleetVoter extends Voter
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof User;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $subject */
        if ($subject->getPublicChoice() === User::PUBLIC_CHOICE_PUBLIC) {
            return true;
        }

        // if not public : we must be logged
        if (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return false;
        }
        /** @var User $me */
        $me = $token->getUser();

        // private only it's myself
        if ($subject->getPublicChoice() === User::PUBLIC_CHOICE_PRIVATE) {
            return $me->getId()->equals($subject->getId());
        }

        if ($subject->getPublicChoice() !== User::PUBLIC_CHOICE_ORGANIZATION) {
            return false;
        }
        $citizen = $subject->getCitizen();
        if ($citizen === null || $me->getCitizen() === null) {
            return false;
        }

        return $this->hasCommonOrga($citizen, $me->getCitizen());
    }

    private function hasCommonOrga(Citizen $citizen1, Citizen $citizen2): bool
    {
        foreach ($citizen1->getOrganizations() as $orga1) {
            foreach ($citizen2->getOrganizations() as $orga2) {
                if ($orga1->getOrganization()->getId()->equals($orga2->getId())) {
                    return true;
                }
            }
        }

        return false;
    }
}
