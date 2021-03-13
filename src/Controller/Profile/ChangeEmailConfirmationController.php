<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChangeEmailConfirmationController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route("/change-email-confirmation", name: "profile_change_email_confirmation", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        $token = $request->query->get('token');
        $userId = $request->query->get('id');

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $userId, 'registrationConfirmationToken' => $token]);
        if ($user === null) {
            return $this->render('security/change_email_confirmation.html.twig', [
                'error' => 'not_exist',
            ]);
        }

        if (!$user->isEmailConfirmed()) {
            return $this->render('security/change_email_confirmation.html.twig', [
                'error' => 'user_not_confirmed',
            ]);
        }

        $user->setEmail($user->getPendingEmail());
        $user->setPendingEmail(null);
        $user->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->render('security/change_email_confirmation.html.twig');
    }
}
