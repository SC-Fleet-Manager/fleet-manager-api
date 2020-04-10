<?php

namespace App\Controller\Profile;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Security;

class ProfileController extends AbstractController
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
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
        $user = $this->security->getUser();

        if (!$this->security->getToken() instanceof SwitchUserToken) {
            // we don't want tracking when impersonation
            $user->setLastConnectedAt(new \DateTimeImmutable());
            $this->entityManager->flush();
        }

        return $this->json($user, 200, [], ['groups' => 'profile']);
    }
}
