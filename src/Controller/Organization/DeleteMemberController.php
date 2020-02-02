<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Organization;
use App\Entity\User;
use App\Event\CitizenFiredEvent;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class DeleteMemberController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $entityManager;
    private OrganizationRepository $organizationRepository;
    private CitizenRepository $citizenRepository;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->organizationRepository = $entityManager->getRepository(Organization::class);
        $this->citizenRepository = $entityManager->getRepository(Citizen::class);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/api/organization/{organizationSid}/delete-member/{citizenId}", name="organization_delete_member", methods={"POST"})
     */
    public function __invoke(string $organizationSid, string $citizenId): Response
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
                'errorMessage' => sprintf('You must be an admin of %s to remove a member. Try to refresh your RSI profile in your <a href="/profile">profile page</a>.', $organization->getName()),
            ], 403);
        }

        /** @var Citizen|null $targetCitizen */
        $targetCitizen = $this->citizenRepository->find($citizenId);
        if ($targetCitizen === null) {
            return $this->json([
                'error' => 'not_found_citizen',
                'errorMessage' => 'The citizen does not exist.',
            ], 404);
        }
        if (null === $citizenOrga = $targetCitizen->getOrgaBySid($organizationSid)) {
            return $this->json([
                'error' => 'citizen_not_in_orga',
                'errorMessage' => sprintf('The citizen "%s" is not in your orga. Try to refresh your RSI profile in your <a href="/profile">profile page</a>.', $targetCitizen->getActualHandle()->getHandle()),
            ], 403);
        }

        $this->entityManager->remove($citizenOrga);
        $this->eventDispatcher->dispatch(new CitizenFiredEvent($targetCitizen, $citizenOrga->getOrganization()));
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
