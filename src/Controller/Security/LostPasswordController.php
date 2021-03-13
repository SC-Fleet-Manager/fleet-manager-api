<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Dto\LostPassword;
use App\Message\Registration\SendLostPasswordRequestMail;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class LostPasswordController extends AbstractController
{
    public function __construct(
        private ValidatorInterface $validator,
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializer,
        private MessageBusInterface $bus,
        private UserRepository $userRepository
    ) {
    }

    #[Route("/api/lost-password", name: "security_lost_password", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        /** @var LostPassword $lostPassword */
        $lostPassword = $this->serializer->deserialize($request->getContent(), LostPassword::class, $request->getContentType());
        $errors = $this->validator->validate($lostPassword);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['email' => $lostPassword->email]);
        if ($user === null) {
            return $this->json(null, 204); // Do not expose the user does not exist
        }

        if (!$user->canBeLostPasswordRequested()) {
            return $this->json(null, 204);
        }

        $user->setLostPasswordToken(User::generateToken());
        $user->setLostPasswordRequestedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->bus->dispatch(new SendLostPasswordRequestMail($user->getId()));

        return $this->json(null, 204);
    }
}
