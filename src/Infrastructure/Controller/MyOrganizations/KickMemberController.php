<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\KickMemberService;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class KickMemberController
{
    public function __construct(
        private KickMemberService $kickMemberService,
        private Security $security,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Response(response=204, description="Kick the given member off the given organization.")
     * @OpenApi\Response(response=400, description="The organization does not exist. Logged user is not the founder of the organization. Given member has not fully joined.")
     */
    #[Route('/api/organizations/manage/{orgaId}/kick-member/{memberId}',
        name: 'organizations_manage_kick_member',
        requirements: ['orgaId' => OrgaId::PATTERN, 'memberId' => MemberId::PATTERN],
        methods: ['POST'],
    )]
    public function __invoke(
        Request $request,
        string $orgaId,
        string $memberId,
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->kickMemberService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
            MemberId::fromString($memberId),
        );

        return new Response(null, 204);
    }
}
