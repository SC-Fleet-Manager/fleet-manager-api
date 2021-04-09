<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\CreateShipService;
use App\Domain\ShipId;
use App\Entity\User;
use App\Infrastructure\Controller\MyFleet\Input\CreateShipInput;
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

class CreateShipController
{
    public function __construct(
        private CreateShipService $createShipService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyFleet")
     * @OpenApi\RequestBody(
     *     @Model(type=CreateShipInput::class)
     * )
     * @OpenApi\Response(response=204, description="Creates a new ship for the logged user's fleet.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/my-fleet/create-ship', name: 'my_fleet_create_ship', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var CreateShipInput $input */
        $input = $this->serializer->deserialize($request->getContent(), CreateShipInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->createShipService->handle($user->getId(), new ShipId(new Ulid()), $input->name, $input->pictureUrl, $input->quantity);

        return new Response(null, 204);
    }
}
