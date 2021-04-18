<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\UnjoinOrganizationService;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class UnjoinOrganizationController
{
    public function __construct(
        private UnjoinOrganizationService $unjoinOrganizationService,
        private Security $security,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Response(response=204, description="Remove the apply of logged user from an organization.")
     * @OpenApi\Response(response=400, description="The organization does not exist.")
     * @OpenApi\Response(response=400, description="Not member of the organization.")
     * @OpenApi\Response(response=400, description="Has fully joined the organization.")
     */
    #[Route('/api/organizations/{orgaId}/unjoin',
        name: 'organizations_unjoin',
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

        $this->unjoinOrganizationService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
        );

        return new Response(null, 204);
    }
}
