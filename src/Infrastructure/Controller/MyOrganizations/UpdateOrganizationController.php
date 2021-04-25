<?php

namespace App\Infrastructure\Controller\MyOrganizations;

use App\Application\MyOrganizations\UpdateOrganizationService;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\User;
use App\Infrastructure\Controller\MyOrganizations\Input\UpdateOrganizationInput;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateOrganizationController
{
    public function __construct(
        private UpdateOrganizationService $updateOrganizationService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyOrganizations")
     * @OpenApi\RequestBody(
     *     @Model(type=UpdateOrganizationInput::class)
     * )
     * @OpenApi\Response(response=204, description="Updates the given organization.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/organizations/{orgaId}/update',
        name: 'my_organizations_update',
        requirements: ['orgaId' => OrgaId::PATTERN],
        methods: ['POST']
    )]
    public function __invoke(
        Request $request,
        string $orgaId,
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var UpdateOrganizationInput $input */
        $input = $this->serializer->deserialize($request->getContent(), UpdateOrganizationInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->updateOrganizationService->handle(
            OrgaId::fromString($orgaId),
            MemberId::fromString((string) $user->getId()),
            $input->name,
            $input->logoUrl,
        );

        return new Response(null, 204);
    }
}
