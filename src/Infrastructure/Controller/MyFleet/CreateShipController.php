<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\Exception\AlreadyExistingFleetForUserException;
use App\Application\MyFleet\CreateShipService;
use App\Domain\ShipId;
use App\Entity\User;
use App\Infrastructure\Controller\MyFleet\Input\CreateShipInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateShipController
{
    public function __construct(
        private CreateShipService $createShipService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/api/create-ship', name: 'create_ship', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var CreateShipInput $input */
        $input = $this->serializer->deserialize($request->getContent(), CreateShipInput::class, $request->getContentType());
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

        try {
            $this->createShipService->handle($user->getId(), new ShipId(new Ulid()), $input->name, $input->pictureUrl);
        } catch (AlreadyExistingFleetForUserException $e) {
            return new JsonResponse($this->serializer->serialize([
                'error' => 'already_existing_fleet',
                'errorMessage' => 'You have already a fleet.',
            ], 'json'), 400, [], true);
        }

        return new Response(null, 204);
    }
}
