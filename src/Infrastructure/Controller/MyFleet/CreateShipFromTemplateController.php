<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\CreateShipFromTemplateService;
use App\Domain\ShipId;
use App\Domain\ShipTemplateId;
use App\Entity\User;
use App\Infrastructure\Controller\MyFleet\Input\CreateShipFromTemplateInput;
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

class CreateShipFromTemplateController
{
    public function __construct(
        private CreateShipFromTemplateService $createShipFromTemplateService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyFleet")
     * @OpenApi\RequestBody(
     *     @Model(type=CreateShipFromTemplateInput::class)
     * )
     * @OpenApi\Response(response=204, description="Creates a new ship (based on a template) for the logged user's fleet.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/my-fleet/create-ship-from-template', name: 'my_fleet_create_ship_from_template', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var CreateShipFromTemplateInput $input */
        $input = $this->serializer->deserialize($request->getContent(), CreateShipFromTemplateInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->createShipFromTemplateService->handle($user->getId(), new ShipId(new Ulid()), ShipTemplateId::fromString($input->templateId), $input->quantity);

        return new Response(null, 204);
    }
}
