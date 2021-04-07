<?php

namespace App\Infrastructure\Controller\Profile;

use App\Application\Profile\DeleteAccountService;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class DeleteAccountController
{
    public function __construct(
        private DeleteAccountService $deleteAccount,
        private Security $security,
    ) {
    }

    /**
     * @OpenApi\Tag(name="Profile")
     * @OpenApi\Get(description="Deletes the logged user's account and all its data.")
     * @OpenApi\Response(response=204, description="Ok.")
     */
    #[Route('/api/profile/delete-account', name: 'profile_delete_account', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->deleteAccount->handle($user->getId());

        return new Response(null, 204);
    }
}
