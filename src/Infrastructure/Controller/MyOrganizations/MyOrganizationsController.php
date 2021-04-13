<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\MyOrganizationsService;
use App\Application\MyOrganizations\Output\MyOrganizationsOutput;
use App\Domain\MemberId;
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

class MyOrganizationsController
{
    public function __construct(
        private MyOrganizationsService $myOrganizationsService,
        private Security $security,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\Get(description="Returns infos about the orgas of logged user.")
     * @OpenApi\Response(response=200, description="Ok.", @Model(type=MyOrganizationsOutput::class))
     */
    #[Route('/api/my-organizations', name: 'my_organizations', methods: ['GET'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->myOrganizationsService->handle(MemberId::fromString((string) $user->getId()));

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
