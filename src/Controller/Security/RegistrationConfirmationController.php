<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationConfirmationController extends AbstractController
{
    private $userRepository;
    private $entityManager;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/register-confirmation", name="security_registration_confirmation", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $token = $request->query->get('token');
        $userId = $request->query->get('id');

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $userId, 'registrationConfirmationToken' => $token]);
        if ($user === null) {
            return $this->render('security/registration_confirmation.html.twig', [
                'error' => 'not_exist',
            ]);
        }

        if ($user->isEmailConfirmed()) {
            return $this->render('security/registration_confirmation.html.twig', [
                'error' => 'already_confirmed',
            ]);
        }

        $user->setEmailConfirmed(true);
        $this->entityManager->flush();

        return $this->render('security/registration_confirmation.html.twig');
    }
}
