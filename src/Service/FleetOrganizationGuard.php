<?php

namespace App\Service;

use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class FleetOrganizationGuard
{
    private $organizationRepository;
    private $citizenRepository;
    private $security;

    public function __construct(OrganizationRepository $organizationRepository, CitizenRepository $citizenRepository, Security $security)
    {
        $this->organizationRepository = $organizationRepository;
        $this->citizenRepository = $citizenRepository;
        $this->security = $security;
    }

    public function checkAccessibleOrganization(string $orgaSid): ?Response
    {
        /** @var Organization $orga */
        $orga = $this->organizationRepository->findOneBy(['organizationSid' => $orgaSid]);
        if ($orga === null) {
            return new JsonResponse([
                'error' => 'bad_organization',
                'errorMessage' => sprintf('The organization %s does not exist.', $orgaSid),
            ], 404);
        }

        if ($orga->getPublicChoice() === Organization::PUBLIC_CHOICE_PUBLIC) {
            return null;
        }

        if (!$this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new JsonResponse([
                'error' => 'not_enough_rights_public',
                'errorMessage' => sprintf('Sorry, you have not the rights to access to %s fleet page.<br/>If you are in this orga, please login and refresh your RSI profile in <a href="/profile">your profile page</a>.', $orga->getName()),
            ], 403);
        }

        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return new JsonResponse([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }
        if (!$citizen->hasOrganization($orgaSid)) {
            return new JsonResponse([
                'error' => 'not_enough_rights_private',
                'errorMessage' => sprintf('Sorry, you have not the rights to access to %s fleet page.<br/>If you are in this orga, try to refresh your RSI profile in <a href="/profile">your profile page</a>.', $orga->getName()),
            ], 403);
        }

        if ($orga->getPublicChoice() === Organization::PUBLIC_CHOICE_PRIVATE) {
            return null;
        }

        // PUBLIC_CHOICE_ADMIN
        $admins = $this->citizenRepository->findAdminByOrganization($orgaSid);
        foreach ($admins as $admin) {
            if ($admin->getId()->equals($citizen->getId())) {
                return null;
            }
        }

        return new JsonResponse([
            'error' => 'not_enough_rights_admin',
            'errorMessage' => sprintf('Sorry, a <b>"%s"</b> of <b>%s</b> has limited the fleet access to admins only.<br/>If you have a greater rank on this orga, try to refresh your RSI profile in <a href="/profile">your profile page</a>.', $admins[0]->getOrgaBySid($orgaSid)->getRankName(), $orga->getName()),
        ], 403);
    }
}
