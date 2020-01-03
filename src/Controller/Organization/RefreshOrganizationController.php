<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use App\Service\Organization\MembersInfosProvider\OrganizationMembersInfosProviderInterface;
use App\Service\Organization\OrganizationMembersUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class RefreshOrganizationController extends AbstractController
{
    private Security $security;
    private OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider;
    private OrganizationMembersUtil $organizationMembersUtil;
    private EntityManagerInterface $entityManager;
    private OrganizationRepository $organizationRepository;

    public function __construct(
        Security $security,
        OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider,
        OrganizationMembersUtil $organizationMembersUtil,
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->organizationMembersInfosProvider = $organizationMembersInfosProvider;
        $this->organizationMembersUtil = $organizationMembersUtil;
        $this->entityManager = $entityManager;
        $this->organizationRepository = $entityManager->getRepository(Organization::class);
    }

    /**
     * @Route("/api/organization/{organizationSid}/refresh-orga", name="organization_refresh_orga", methods={"POST"})
     */
    public function __invoke(string $organizationSid, OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

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

        if (!$organization->canBeRefreshed()) {
            return $this->json([
                'error' => 'too_many_refresh',
                'errorMessage' => sprintf('Sorry, you have to wait %s minutes before refreshing.', $organization->getTimeLeftBeforeRefreshing()->format('%i')),
            ], 400);
        }

        $memberInfos = $this->organizationMembersInfosProvider->retrieveInfos(new SpectrumIdentification($organizationSid), false);
        if (isset($memberInfos['error']) && $memberInfos['error'] === 'orga_too_big') {
            return $this->json([
                'error' => 'orga_too_big',
                'errorMessage' => 'Sorry, your orga is too big to retrieve the members list right now. We\'re currently searching a solution for this issue.',
            ], 400);
        }
        $organization->setLastRefresh(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json($this->organizationMembersUtil->renderMembersList($memberInfos));
    }
}
