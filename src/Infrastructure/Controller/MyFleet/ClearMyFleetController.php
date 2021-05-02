<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\ClearMyFleetService;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class ClearMyFleetController
{
    public function __construct(
        private ClearMyFleetService $clearMyFleetService,
        private Security $security,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyFleet")
     * @OpenApi\Response(response=204, description="Deletes all ships of the logged user.")
     */
    #[Route('/api/my-fleet/clear', name: 'my_fleet_clear', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->clearMyFleetService->handle($user->getId());

        return new Response(null, 204);
    }
}
