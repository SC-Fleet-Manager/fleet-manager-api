<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\AcceptCandidateService;
use App\Application\MyOrganizations\DeclineCandidateService;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

class AcceptCandidateController
{
    public function __construct(
        private AcceptCandidateService $acceptCandidateService,
        private Security $security,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Response(response=204, description="Accept the given candidate from the given organization.")
     * @OpenApi\Response(response=400, description="The organization does not exist. Logged user is not the founder of the organization.")
     */
    #[Route('/api/organizations/manage/{orgaId}/accept-candidate/{candidateId}',
        name: 'organizations_manage_accept_candidate',
        requirements: ['orgaId' => OrgaId::PATTERN, 'candidateId' => MemberId::PATTERN],
        methods: ['POST'],
    )]
    public function __invoke(
        Request $request,
        string $orgaId,
        string $candidateId,
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->acceptCandidateService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
            MemberId::fromString($candidateId),
        );

        return new Response(null, 204);
    }
}
