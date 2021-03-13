<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Form\Dto\ChangeEmail;
use App\Message\Profile\SendChangeEmailRequestMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangeEmailRequestController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        private MessageBusInterface $bus
    ) {
    }

    #[Route("/api/profile/change-email-request", name: "profile_change_email_request", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        if (!$user->isEmailConfirmed()) {
            return $this->json([
                'error' => 'user_not_confirmed',
                'errorMessage' => 'You can\'t do that now. First, you have to confirm your email address. Please check your inbox/spams.',
            ], 400);
        }

        /** @var ChangeEmail $changeEmail */
        $changeEmail = $this->serializer->denormalize($request->request->all(), ChangeEmail::class);
        $errors = $this->validator->validate($changeEmail);

        // TODO : extract in a Constraint
        if ($user->getEmail() === $changeEmail->newEmail) {
            $errors->add(new ConstraintViolation('This is your current email address. Please choose another.', null, [], null, 'newEmail', null));
        }

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $user->setRegistrationConfirmationToken(User::generateToken());
        $user->setPendingEmail($changeEmail->newEmail);
        $this->entityManager->flush();

        $this->bus->dispatch(new SendChangeEmailRequestMail($user->getId()));

        return $this->json(null, 204);
    }
}
