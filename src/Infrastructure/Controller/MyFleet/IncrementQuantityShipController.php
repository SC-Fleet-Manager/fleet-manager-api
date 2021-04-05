<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\IncrementQuantityShipService;
use App\Domain\ShipId;
use App\Entity\User;
use App\Infrastructure\Controller\MyFleet\Input\IncrementQuantityShipInput;
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

    #[Route('/api/increment-quantity-ship/{shipId}',
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
        $errors = $this->validator->validate($input);
        if ($errors->count() > 0) {
            $json = $this->serializer->serialize([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 'json');

            return new JsonResponse($json, 400, [], true);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->incrementQuantityShipService->handle($user->getId(), ShipId::fromString($shipId), $input->step ?? 1);

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
