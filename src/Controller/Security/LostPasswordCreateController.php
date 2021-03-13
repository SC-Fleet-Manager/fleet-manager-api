<?php

namespace App\Controller\Security;

use App\Entity\User;
use App\Form\Dto\LostPasswordCreate;
use App\Form\LostPasswordCreateForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LostPasswordCreateController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
        private UserPasswordEncoderInterface $passwordEncoder
    ) {
    }

    #[Route("/lost-password-create", name: "security_lost_password_create", methods: ["GET", "POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $token = $request->query->get('token');
        $userId = $request->query->get('id');

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['id' => $userId, 'lostPasswordToken' => $token]);
        if ($user === null) {
            return $this->render('security/lost_password_create.html.twig', [
                'error' => 'not_exist',
            ]);
        }

        if ($user->isLostPasswordRequestExpired()) {
            return $this->render('security/lost_password_create.html.twig', [
                'error' => 'token_expired',
            ]);
        }

        $lostPasswordCreate = new LostPasswordCreate();
        $form = $this->createForm(LostPasswordCreateForm::class, $lostPasswordCreate);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordEncoder->encodePassword($user, $lostPasswordCreate->password));
            $this->entityManager->flush();

            return $this->render('security/lost_password_create.html.twig', [
                'success' => true,
            ]);
        }

        return $this->render('security/lost_password_create.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }
}
