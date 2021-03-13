<?php

namespace App\Controller\Security;

use App\Message\Registration\SendRegistrationConfirmationMail;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResendRegistrationConfirmationController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private MessageBusInterface $bus,
        private UserRepository $userRepository
    ) {
    }

    #[Route("/api/resend-registration-confirmation", name: "security_resend_registration_confirmation", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $username = $request->request->get('username');
        $errors = $this->validator->validate($username, [
            new NotBlank(),
        ]);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $user = $this->userRepository->findOneBy(['email' => $username]);
        if ($user !== null) {
            $this->bus->dispatch(new SendRegistrationConfirmationMail($user->getId()));
        }

        return $this->json(null, 204);
    }
}
