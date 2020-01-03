<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use App\Service\Citizen\CitizenRefresher;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class RefreshMemberController extends AbstractController
{
    private Security $security;
    private CitizenInfosProviderInterface $citizenInfosProvider;
    private CitizenRefresher $citizenRefresher;
    private EntityManagerInterface $entityManager;
    private OrganizationRepository $organizationRepository;
    private CitizenRepository $citizenRepository;

    public function __construct(
        Security $security,
        CitizenInfosProviderInterface $citizenInfosProvider,
        CitizenRefresher $citizenRefresher,
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->citizenRefresher = $citizenRefresher;
        $this->entityManager = $entityManager;
        $this->organizationRepository = $entityManager->getRepository(Organization::class);
        $this->citizenRepository = $entityManager->getRepository(Citizen::class);
    }

    /**
     * @Route("/api/organization/{organizationSid}/refresh-member/{handle}", name="organization_refresh_member", methods={"POST"})
     */
    public function __invoke(string $organizationSid, string $handle): Response
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

        /** @var Citizen|null $targetCitizen */
        $targetCitizen = $this->citizenRepository->findOneBy(['actualHandle' => $handle]);
        if ($targetCitizen === null) {
            return $this->json([
                'error' => 'not_found_citizen',
                'errorMessage' => sprintf('The citizen "%s" does not exist.', $handle),
            ], 404);
        }

        $citizenInfos = $this->citizenInfosProvider->retrieveInfos(clone $targetCitizen->getActualHandle(), false);
        if (!$citizenInfos->numberSC->equals($targetCitizen->getNumber())) {
            return $this->json([
                'error' => 'bad_citizen',
                'errorMessage' => sprintf('The SC handle of %s has probably changed. He should update it in its Profile.', $targetCitizen->getActualHandle()->getHandle()),
            ], 400);
        }

        $this->citizenRefresher->refreshCitizen($targetCitizen, $citizenInfos);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
