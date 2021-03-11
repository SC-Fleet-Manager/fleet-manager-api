<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Dto\Registration;
use App\Message\Registration\SendRegistrationConfirmationMail;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    private ValidatorInterface $validator;
    private EntityManagerInterface $entityManager;
    private UserPasswordEncoderInterface $passwordEncoder;
    private SerializerInterface $serializer;
    private MessageBusInterface $bus;

    public function __construct(
        ValidatorInterface $validator,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        SerializerInterface $serializer,
        MessageBusInterface $bus
    ) {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->serializer = $serializer;
        $this->bus = $bus;
    }

    /**
     * @Route("/api/register", name="security_registration", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        /** @var Registration $registration */
        $registration = $this->serializer->deserialize($request->getContent(), Registration::class, $request->getContentType());
        $errors = $this->validator->validate($registration);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $newUser = (new User(Uuid::uuid4()))
            ->setEmail($registration->email)
            ->setToken(User::generateToken())
            ->setApiToken(User::generateToken())
            ->setRegistrationConfirmationToken(User::generateToken());
        $newUser->setPassword($this->passwordEncoder->encodePassword($newUser, $registration->password));
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        $this->bus->dispatch(new SendRegistrationConfirmationMail($newUser->getId()));

        return $this->json(null, 204);
    }
}
