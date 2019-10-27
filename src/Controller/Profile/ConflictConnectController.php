<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ConflictConnectController extends AbstractController
{
    private $security;
    private $userRepository;

    public function __construct(Security $security, UserRepository $userRepository)
    {
        $this->security = $security;
        $this->userRepository = $userRepository;
    }

    /**
     * @Route("/api/profile/conflict-connect/discord", name="profile_conflict_connect", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        if ($user->getPendingDiscordId() === null) {
            return $this->json([
                'error' => 'no_pending_discord',
                'errorMessage' => 'You must link your Discord account before.',
            ], 400);
        }

        /** @var User $alreadyLinkedUser */
        $alreadyLinkedUser = $this->userRepository->findOneBy(['discordId' => $user->getPendingDiscordId()]);
        if ($alreadyLinkedUser === null || $alreadyLinkedUser->getId()->equals($user->getId())) {
            return $this->json([
                'error' => 'no_linked_user',
                'errorMessage' => 'There is no other users with your Discord account.',
            ], 400);
        }

        return $this->json([
            'me' => $user,
            'alreadyLinkedUser' => $alreadyLinkedUser,
        ], 200, [], ['groups' => ['profile']]);
    }
}
