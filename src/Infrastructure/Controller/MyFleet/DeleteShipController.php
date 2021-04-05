<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Domain\Exception\ConflictVersionException;
use App\Domain\Exception\NotFoundFleetByUserException;
use App\Application\MyFleet\DeleteShipService;
use App\Domain\Exception\NotFoundShipException;
use App\Domain\ShipId;
use App\Entity\User;
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

    #[Route('/api/delete-ship/{shipId}',
        name: 'delete_ship',
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
