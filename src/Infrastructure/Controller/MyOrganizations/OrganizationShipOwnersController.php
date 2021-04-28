<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\OrganizationCandidatesService;
use App\Application\MyOrganizations\OrganizationShipOwnersService;
use App\Application\MyOrganizations\Output\OrganizationCandidatesOutput;
use App\Application\MyOrganizations\Output\OrganizationShipOwnersOutput;
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

class OrganizationShipOwnersController
{
    public function __construct(
        private OrganizationShipOwnersService $organizationShipOwnersService,
        private Security $security,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Get(description="Returns owners of given organization ship.")
     * @OpenApi\Response(response=200, description="Ok.", @Model(type=OrganizationShipOwnersOutput::class))
     * @OpenApi\Response(response=400, description="The organization does not exist. Logged user has not joined the organization.")
     */
    #[Route('/api/organizations/{orgaId}/ship/{shipModel}/owners',
        name: 'organizations_ship_owners',
        requirements: ['orgaId' => OrgaId::PATTERN],
        methods: ['GET']
    )]
    public function __invoke(
        Request $request,
        string $orgaId,
        string $shipModel,
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->organizationShipOwnersService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
            $shipModel
        );

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
