<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\OrganizationsService;
use App\Application\MyOrganizations\Output\OrganizationsCollectionOutput;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class OrganizationsController
{
    private const ITEMS_PER_PAGE = 20;

    public function __construct(
        private OrganizationsService $organizationsService,
        private Security $security,
        private SerializerInterface $serializer,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Get(description="Returns infos about all orgas.")
     * @OpenApi\Parameter(
     *     in="path",
     *     name="sinceId",
     *     schema=@OpenApi\Property(type="string", format="uid"),
     *     example="00000000-0000-0000-0000-000000000001"
     * )
     * @OpenApi\Response(response=200, description="Ok.", @Model(type=OrganizationsCollectionOutput::class))
     */
    #[Route('/api/organizations', name: 'organizations', methods: ['GET'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $sinceId = null;
        if ($request->query->has('sinceId')) {
            try {
                $sinceId = OrgaId::fromString($request->query->get('sinceId'));
            } catch (\Throwable) {
                // pass
            }
        }

        $output = $this->organizationsService->handle(
            MemberId::fromString((string) $user->getId()),
            $this->urlGenerator->generate('organizations', [], UrlGeneratorInterface::ABSOLUTE_URL),
            self::ITEMS_PER_PAGE,
            $sinceId,
        );

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
