<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\UpdateShipFromTemplateService;
use App\Domain\ShipId;
use App\Domain\ShipTemplateId;
use App\Entity\User;
use App\Infrastructure\Controller\MyFleet\Input\UpdateShipFromTemplateInput;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateShipFromTemplateController
{
    public function __construct(
        private UpdateShipFromTemplateService $updateShipFromTemplateService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyFleet")
     * @OpenApi\RequestBody(
     *     @Model(type=UpdateShipFromTemplateInput::class)
     * )
     * @OpenApi\Response(response=204, description="Updates an existing ship (based on a template) for the logged user's fleet.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/my-fleet/update-ship-from-template/{shipId}',
        name: 'my_fleet_update_ship_from_template',
        requirements: ['shipId' => ShipId::PATTERN],
        methods: ['POST']),
    ]
    public function __invoke(
        Request $request,
        string $shipId,
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var UpdateShipFromTemplateInput $input */
        $input = $this->serializer->deserialize($request->getContent(), UpdateShipFromTemplateInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->updateShipFromTemplateService->handle($user->getId(), ShipId::fromString($shipId), ShipTemplateId::fromString($input->templateId), $input->quantity);

        return new Response(null, 204);
    }
}
