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
        $this->denyAccessUnlessGranted('ROLE_USER');

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
        $user->setSupporterVisible($preferences->supporterVisible);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
