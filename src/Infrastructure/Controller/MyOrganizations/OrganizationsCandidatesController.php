<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\OrganizationCandidatesService;
use App\Application\MyOrganizations\Output\OrganizationCandidatesOutput;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class OrganizationsCandidatesController
{
    public function __construct(
        private OrganizationCandidatesService $organizationCandidatesService,
        private Security $security,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Get(description="Returns candidates (who applied) of the given organization.")
     * @OpenApi\Response(response=200, description="Ok.", @Model(type=OrganizationCandidatesOutput::class))
     * @OpenApi\Response(response=400, description="Logged user is not the founder of the organization.")
     */
    #[Route('/api/organizations/manage/{orgaId}/candidates',
        name: 'organizations_manage_candidates',
        requirements: ['orgaId' => OrgaId::PATTERN],
        methods: ['GET']
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

        $output = $this->organizationCandidatesService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
        );

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
