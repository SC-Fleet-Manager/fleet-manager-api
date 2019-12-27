<?php

namespace App\Controller\Organization;

use App\Domain\SpectrumIdentification;
use App\Entity\Organization;
use App\Entity\User;
use App\Event\OrganizationPolicyChangedEvent;
use App\Form\Dto\OrgaPreferences;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SavePreferencesController extends AbstractController
{
    private Security $security;
    private OrganizationRepository $organizationRepository;
    private EntityManagerInterface $entityManager;
    private EventDispatcherInterface $eventDispatcher;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        Security $security,
        OrganizationRepository $organizationRepository,
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->security = $security;
        $this->organizationRepository = $organizationRepository;
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @Route("/api/organization/{organizationSid}/save-preferences", name="organization_save_preferences", methods={"POST"})
     */
    public function __invoke(Request $request, string $organizationSid): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

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

        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }

        /** @var OrgaPreferences $preferences */
        $preferences = $this->serializer->deserialize($request->getContent(), OrgaPreferences::class, $request->getContentType());
        $errors = $this->validator->validate($preferences);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $oldPublicChoice = $organization->getPublicChoice();

        $organization->setPublicChoice($preferences->publicChoice);
        $organization->setSupporterVisible($preferences->supporterVisible);
        $this->entityManager->flush();

        if ($organization->getPublicChoice() !== $oldPublicChoice) {
            $this->eventDispatcher->dispatch(new OrganizationPolicyChangedEvent(
                $citizen, $organization, $oldPublicChoice, $organization->getPublicChoice()
            ));
        }

        return $this->json(null, 204);
    }
}
