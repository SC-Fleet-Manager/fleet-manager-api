<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class AssociatedCitizensController extends AbstractController
{
    private Security $security;
    private OrganizationRepository $organizationRepository;

    public function __construct(
        Security $security,
        OrganizationRepository $organizationRepository
    ) {
        $this->security = $security;
        $this->organizationRepository = $organizationRepository;
    }

    /**
     * @Route("/api/organization/{organizationSid}/associated-citizens", name="organization_associated_citizens", methods={"GET"})
     */
    public function __invoke(string $organizationSid): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        // TODO : [optimization] pagination
//        $page = $request->query->getInt('page', 1);
//        $page = $page >= 1 ? $page : 1;
//        $itemsPerPage = 50;

        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }
        /** @var Organization|null $organization */
        $organization = $this->organizationRepository->findOneBy(['organizationSid' => $organizationSid]);
        if ($organization === null) {
            return $this->json([
                'error' => 'not_found_orga',
                'errorMessage' => sprintf('The organization "%s" does not exist.', $organizationSid),
            ], 404);
        }

        if (!$this->isGranted('IS_ADMIN_MANAGEABLE', new SpectrumIdentification($organizationSid))) {
            return $this->json([
                'error' => 'not_enough_rights',
                'errorMessage' => sprintf('You must be an admin of %s to view these stats. Try to refresh your RSI profile in your <a href="/profile">profile page</a>.', $organization->getName()),
            ], 403);
        }

        $citizens = $this->organizationRepository->findCitizensBySid($organizationSid);

        return $this->json($citizens, 200, [], ['groups' => 'public_profile']);
    }
}
