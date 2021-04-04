<?php

namespace App\Infrastructure\Controller\Profile;

use App\Application\Profile\SavePreferencesService;
use App\Entity\User;
use App\Infrastructure\Controller\Profile\Input\SavePreferencesInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SavePreferencesController
{
    public function __construct(
        private SavePreferencesService $savePreferencesService,
        private Security $security,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    #[Route("/api/profile/save-preferences", name: "profile_save_preferences", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var SavePreferencesInput $input */
        $input = $this->serializer->deserialize($request->getContent(), SavePreferencesInput::class, $request->getContentType());
        $errors = $this->validator->validate($input);
        if ($errors->count() > 0) {
            $json = $this->serializer->serialize([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 'json');

            return new JsonResponse($json, 400, [], true);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->savePreferencesService->handle($user->getId(), $input->supporterVisible);

        return new JsonResponse(null, 204);
    }
}
