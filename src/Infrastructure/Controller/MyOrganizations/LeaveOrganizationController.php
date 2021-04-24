<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\LeaveOrganizationService;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class LeaveOrganizationController
{
    public function __construct(
        private LeaveOrganizationService $leaveOrganizationService,
        private Security $security,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Response(response=204, description="Leave the given organization and its associated ships.")
     * @OpenApi\Response(response=400, description="The organization does not exist. Logged user is not a member or is a founder of the organization.")
     */
    #[Route('/api/organizations/{orgaId}/leave',
        name: 'organizations_leave',
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

        $this->leaveOrganizationService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
        );

        return new Response(null, 204);
    }
}
