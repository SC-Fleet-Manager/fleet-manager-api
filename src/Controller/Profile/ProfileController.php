<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ProfileController extends AbstractController
{
    private Security $security;
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * Retrieves profile infos : user properties.
     *
     * @Route("/api/profile", name="profile", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->userRepository->find($this->security->getUser()->getId());
        $this->entityManager->refresh($user);

        return $this->json($user, 200, [], ['groups' => 'profile']);
    }
}
