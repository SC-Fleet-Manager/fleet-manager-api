<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\CreateShipService;
use App\Application\MyFleet\UpdateShipService;
use App\Domain\ShipId;
use App\Entity\User;
use App\Infrastructure\Controller\MyFleet\Input\CreateShipInput;
use App\Infrastructure\Controller\MyFleet\Input\UpdateShipInput;
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

class UpdateShipController
{
    public function __construct(
        private UpdateShipService $updateShipService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyFleet")
     * @OpenApi\Parameter(
     *     name="shipId",
     *     in="path",
     *     description="The ship to update.",
     *     schema=@OpenApi\Property(type="string", format="uid"),
     *     example="00000000-0000-0000-0000-000000000001"
     * )
     * @OpenApi\RequestBody(
     *     @Model(type=UpdateShipInput::class)
     * )
     * @OpenApi\Response(response=204, description="Updates a ship of the logged user.")
     * @OpenApi\Response(response=404, description="The user has no fleet or the ship does not exist.")
     */
    #[Route('/api/my-fleet/update-ship/{shipId}',
        name: 'my_fleet_update_ship',
        requirements: ['shipId' => ShipId::PATTERN],
        methods: ['POST'],
    )]
    public function __invoke(
        Request $request,
        string $shipId
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var UpdateShipInput $input */
        $input = $this->serializer->deserialize($request->getContent(), UpdateShipInput::class, $request->getContentType());
        $input->shipId = ShipId::fromString($shipId);
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->updateShipService->handle($user->getId(), ShipId::fromString($shipId), $input->name, $input->pictureUrl, $input->quantity);

        return new Response(null, 204);
    }
}
