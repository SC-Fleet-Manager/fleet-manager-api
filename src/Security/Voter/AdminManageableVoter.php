<?php

namespace App\Security\Voter;

use App\Domain\SpectrumIdentification;
use App\Entity\User;
use App\Repository\CitizenRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AdminManageableVoter extends Voter
{
    private $citizenRepository;

    public function __construct(CitizenRepository $citizenRepository)
    {
        $this->citizenRepository = $citizenRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        return $subject instanceof SpectrumIdentification && $attribute === 'IS_ADMIN_MANAGEABLE';
    }

    /**
     * @param SpectrumIdentification $organizationSid
     * @param mixed                  $attribute
     */
    protected function voteOnAttribute($attribute, $organizationSid, TokenInterface $token): bool
    {
        /** @var User $me */
        $me = $token->getUser();
        if (!$me instanceof User) {
            return false;
        }
        $viewerCitizen = $me->getCitizen();
        if ($viewerCitizen === null) {
            return false;
        }

        $admins = $this->citizenRepository->findAdminByOrganization($organizationSid->getSid());
        // Am I an admin of this orga?
        foreach ($admins as $admin) {
            if ($admin->getId()->equals($viewerCitizen->getId())) {
                return true;
            }
        }

        return false;
    }
}
