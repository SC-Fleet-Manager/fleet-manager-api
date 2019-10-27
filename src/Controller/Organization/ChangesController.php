<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Organization;
use App\Entity\OrganizationChange;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ChangesController extends AbstractController
{
    private $security;
    private $organizationRepository;
    private $entityManager;

    public function __construct(Security $security, OrganizationRepository $organizationRepository, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->organizationRepository = $organizationRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/organization/{organizationSid}/changes", name="organization_changes", methods={"GET"})
     */
    public function __invoke(string $organizationSid): Response
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

        $changes = $this->entityManager->getRepository(OrganizationChange::class)->findBy(['organization' => $organization], ['createdAt' => 'DESC'], 50);

        return $this->json($changes, 200, [], ['groups' => 'orga_fleet_admin']);
    }
}
