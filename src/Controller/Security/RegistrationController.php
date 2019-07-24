<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Dto\Registration;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegistrationController extends AbstractController
{
    private $validator;
    private $entityManager;
    private $passwordEncoder;
    private $serializer;

    public function __construct(ValidatorInterface $validator, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, SerializerInterface $serializer)
    {
        $this->validator = $validator;
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/api/register", name="security_registration", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        $registration = $this->serializer->deserialize($request->getContent(), Registration::class, $request->getContentType());
        $errors = $this->validator->validate($registration);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $newUser = new User(Uuid::uuid4());
        $newUser->setUsername($registration->email);
        $newUser->setToken(User::generateToken());
        $newUser->setApiToken(User::generateToken());
        $newUser->setPassword($this->passwordEncoder->encodePassword($newUser, $registration->password));
        unset($registration);
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        // TODO : send mail for confirmation email

        return $this->json(null, 204);
    }
}
