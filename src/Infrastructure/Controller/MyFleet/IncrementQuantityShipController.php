<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\IncrementQuantityShipService;
use App\Application\MyFleet\Output\IncrementQuantityShipOutput;
use App\Domain\ShipId;
use App\Entity\User;
use App\Infrastructure\Controller\MyFleet\Input\IncrementQuantityShipInput;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class IncrementQuantityShipController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private IncrementQuantityShipService $incrementQuantityShipService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyFleet")
     * @OpenApi\Post(description="Increment or decrement the quantity of a ship of the logged user. Cannot be less than 1.")
     * @OpenApi\Parameter(
     *     name="shipId",
     *     in="path",
     *     description="The ship to delete.",
     *     schema=@OpenApi\Property(type="string", format="uid"),
     *     example="00000000-0000-0000-0000-000000000001"
     * )
     * @OpenApi\RequestBody(
     *     @Model(type=IncrementQuantityShipInput::class)
     * )
     * @OpenApi\Response(response=200, description="Quantity updated.", @Model(type=IncrementQuantityShipOutput::class))
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/my-fleet/increment-quantity-ship/{shipId}',
        name: 'increment_quantity_ship',
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

        /** @var IncrementQuantityShipInput $input */
        $input = $this->serializer->deserialize($request->getContent(), IncrementQuantityShipInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->incrementQuantityShipService->handle($user->getId(), ShipId::fromString($shipId), $input->step ?? 1);

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
