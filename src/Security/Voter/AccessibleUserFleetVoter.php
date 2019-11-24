<?php

namespace App\Security\Voter;

use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;

class AccessibleUserFleetVoter extends Voter
{
    private $security;
    private $citizenRepository;

    public function __construct(Security $security, CitizenRepository $citizenRepository)
    {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof User && $attribute === 'ACCESS_USER_FLEET';
    }

    /**
     * @param User $subject
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if ($subject->getPublicChoice() === User::PUBLIC_CHOICE_PUBLIC) {
            return true;
        }

        // if not public : we must be logged
        if (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return false;
        }
        /** @var User $me */
        $me = $token->getUser();
        if (!$me instanceof User) {
            return false;
        }

        // private only if it's myself
        if ($subject->getPublicChoice() === User::PUBLIC_CHOICE_PRIVATE) {
            return $me->getId()->equals($subject->getId());
        }

        if ($subject->getPublicChoice() !== User::PUBLIC_CHOICE_ORGANIZATION) {
            return false;
        }
        $citizen = $subject->getCitizen();
        $viewerCitizen = $me->getCitizen();
        if ($citizen === null || $viewerCitizen === null) {
            return false;
        }

        // Orga only
        // compare my (subject) orgas and viewer (me) orgas
        // ** if one case show the fleet then display it ** (visible > not visible)
        $commonOrgas = $this->getCommonOrgas($citizen, $me->getCitizen());

        // there is a common orga that is visibility ORGA ?
        foreach ($commonOrgas as $commonOrga) {
            if ($commonOrga->getVisibility() === CitizenOrganization::VISIBILITY_ORGA) {
                return true;
            }
        }

        // there is a common orga that is visibility ADMIN
        // AND viewer is ADMIN of that same orga
        foreach ($commonOrgas as $commonOrga) {
            if ($commonOrga->getVisibility() === CitizenOrganization::VISIBILITY_ADMIN) {
                // viewer is ADMIN of this orga ?
                $admins = $this->citizenRepository->findAdminByOrganization($commonOrga->getOrganization()->getOrganizationSid());
                foreach ($admins as $admin) {
                    if ($admin->getId()->equals($viewerCitizen->getId())) {
                        return true;
                    }
                }
            }
        }

        // visibility PRIVATE

        return false;
    }

    /**
     * @return CitizenOrganization[] owned by $viewedCitizen
     */
    private function getCommonOrgas(Citizen $viewedCitizen, Citizen $viewerCitizen): array
    {
        $res = [];
        foreach ($viewedCitizen->getOrganizations() as $viewedOrga) {
            foreach ($viewerCitizen->getOrganizations() as $viewerOrga) {
                if ($viewedOrga->getOrganization()->getId()->toString($viewerOrga->getOrganization()->getId())) {
                    $res[] = $viewedOrga;
                    break;
                }
            }
        }

        return $res;
    }
}
