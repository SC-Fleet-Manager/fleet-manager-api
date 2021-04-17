<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\JoinOrganizationService;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class JoinOrganizationController
{
    public function __construct(
        private JoinOrganizationService $joinOrganizationService,
        private Security $security,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Response(response=204, description="Creates a new organization with the logged user as funder.")
     * @OpenApi\Response(response=400, description="Orga does not exist.")
     * @OpenApi\Response(response=400, description="Already member of this orga.")
     */
    #[Route('/api/organizations/{orgaId}/join',
        name: 'organizations_join',
        requirements: ['orgaId' => OrgaId::PATTERN],
        methods: ['POST'],
    )]
    public function __invoke(
        Request $request,
        string $orgaId,
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->joinOrganizationService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
        );

        return new Response(null, 204);
    }
}
