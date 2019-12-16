<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Organization;
use App\Entity\User;
use App\Event\OrganizationPolicyChangedEvent;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SavePreferencesController extends AbstractController
{
    private $security;
    private $organizationRepository;
    private $entityManager;
    private $eventDispatcher;

    public function __construct(Security $security, OrganizationRepository $organizationRepository, EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->security = $security;
        $this->organizationRepository = $organizationRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/api/organization/{organizationSid}/save-preferences", name="organization_save_preferences", methods={"POST"}, condition="request.getContentType() == 'json'")
     */
    public function __invoke(Request $request, string $organizationSid): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        $content = json_decode($request->getContent(), true);

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
                'errorMessage' => sprintf('You must be an admin of %s to change their settings. Try to refresh your RSI profile in your <a href="/profile">profile page</a>.', $organization->getName()),
            ], 403);
        }

        if (!isset($content['publicChoice'])) {
            return $this->json([
                'error' => 'invalid_form',
                'errorMessage' => 'The field publicChoice must not be blank.',
            ], 400);
        }

        $oldPublicChoice = $organization->getPublicChoice();

        $organization->setPublicChoice($content['publicChoice']);
        $this->entityManager->flush();

        if ($organization->getPublicChoice() !== $oldPublicChoice) {
            $this->eventDispatcher->dispatch(new OrganizationPolicyChangedEvent(
                $citizen, $organization, $oldPublicChoice, $organization->getPublicChoice()
            ));
        }

        return $this->json(null, 204);
    }
}
