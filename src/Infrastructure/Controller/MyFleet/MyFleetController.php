<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\MyFleetService;
use App\Application\MyFleet\Output\MyFleetOutput;
use App\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class MyFleetController
{
    public function __construct(
        private MyFleetService $myFleetService,
        private Security $security,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyFleet")
     * @OpenApi\Get(description="Retrieve the logged user's fleet with all its ships.")
     * @OpenApi\Response(response=200, description="Ok.", @Model(type=MyFleetOutput::class))
     * @OpenApi\Response(response=400, description="The user has no fleet.")
     */
    #[Route('/api/my-fleet', name: 'my_fleet', methods: ['GET'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->myFleetService->handle($user->getId());

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
