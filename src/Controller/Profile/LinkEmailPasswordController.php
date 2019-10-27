<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Form\Dto\Registration;
use App\Message\Profile\SendLinkEmailPasswordConfirmationMail;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LinkEmailPasswordController extends AbstractController
{
    private $security;
    private $entityManager;
    private $serializer;
    private $validator;
    private $bus;
    private $passwordEncoder;

    public function __construct(
        Security $security,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        MessageBusInterface $bus,
        UserPasswordEncoderInterface $userPasswordEncoder
    ) {
        $this->security = $security;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->bus = $bus;
        $this->passwordEncoder = $userPasswordEncoder;
    }

    /**
     * @Route("/api/profile/link-email-password", name="profile_link_email_password", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        if ($user->isEmailConfirmed()) {
            return $this->json([
                'error' => 'user_confirmed',
                'errorMessage' => 'You are already registered with your email.',
            ], 400);
        }
        if ($user->getEmail() !== null) {
            $this->bus->dispatch(new SendLinkEmailPasswordConfirmationMail($user->getId()));

            return $this->json([
                'error' => 'user_need_confirmation',
                'errorMessage' => 'You must confirm your email address. Please check your inbox/spams, we have sent you a new email.',
            ], 400);
        }

        /** @var Registration $registration */
        $registration = $this->serializer->denormalize($request->request->all(), Registration::class);
        $errors = $this->validator->validate($registration);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $user
            ->setEmail($registration->email)
            ->setRegistrationConfirmationToken(User::generateToken())
            ->setPassword($this->passwordEncoder->encodePassword($user, $registration->password));
        unset($registration); // prevent leak password
        $this->entityManager->flush();

        $this->bus->dispatch(new SendLinkEmailPasswordConfirmationMail($user->getId()));

        return $this->json(null, 204);
    }
}
