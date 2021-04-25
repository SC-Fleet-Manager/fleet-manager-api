<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\AcceptCandidateService;
use App\Application\MyOrganizations\DisbandOrganizationService;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class DisbandOrganizationController
{
    public function __construct(
        private DisbandOrganizationService $disbandOrganizationService,
        private Security $security,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Response(response=204, description="Disband the given organization.")
     * @OpenApi\Response(response=400, description="The organization does not exist. Logged user is not the founder of the organization.")
     */
    #[Route('/api/organizations/manage/{orgaId}/disband',
        name: 'organizations_manage_disband',
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

        $this->disbandOrganizationService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
        );

        return new Response(null, 204);
    }
}
