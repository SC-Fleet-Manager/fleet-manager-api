<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Form\Dto\ProfilePreferences;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SavePreferencesController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route("/api/profile/save-preferences", name: "profile_save_preferences", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var ProfilePreferences $preferences */
        $preferences = $this->serializer->deserialize($request->getContent(), ProfilePreferences::class, $request->getContentType());
        $errors = $this->validator->validate($preferences);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
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

        $user->setSupporterVisible($preferences->supporterVisible);

        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
