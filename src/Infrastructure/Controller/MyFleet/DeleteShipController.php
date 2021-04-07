<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\DeleteShipService;
use App\Domain\ShipId;
use App\Entity\User;
use OpenApi\Annotations as OpenApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DeleteShipController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private DeleteShipService $deleteShipService,
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
     *     description="The ship to delete.",
     *     schema=@OpenApi\Property(type="string", format="uid"),
     *     example="00000000-0000-0000-0000-000000000001"
     * )
     * @OpenApi\Response(response=204, description="Deletes a ship of the logged user.")
     * @OpenApi\Response(response=404, description="The user has no fleet.")
     */
    #[Route('/api/my-fleet/delete-ship/{shipId}',
        name: 'my_fleet_delete_ship',
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

        /** @var User $user */
        $user = $this->security->getUser();

        $this->deleteShipService->handle($user->getId(), ShipId::fromString($shipId));

        return new Response(null, 204);
    }
}
