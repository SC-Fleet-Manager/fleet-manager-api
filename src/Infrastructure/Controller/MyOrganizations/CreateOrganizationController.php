<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\CreateOrganizationService;
use App\Domain\OrgaId;
use App\Entity\User;
use App\Infrastructure\Controller\MyOrganizations\Input\CreateOrganizationInput;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrganizationController
{
    public function __construct(
        private CreateOrganizationService $createOrganizationService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\RequestBody(
     *     @Model(type=CreateOrganizationInput::class)
     * )
     * @OpenApi\Response(response=204, description="Creates a new organization with the logged user as funder.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/organizations/create', name: 'my_organizations_create', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var CreateOrganizationInput $input */
        $input = $this->serializer->deserialize($request->getContent(), CreateOrganizationInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->createOrganizationService->handle(new OrgaId(new Ulid()), $user->getId(), $input->name, $input->sid, $input->logoUrl);

        return new Response(null, 204);
    }
}
