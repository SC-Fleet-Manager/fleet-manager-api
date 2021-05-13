<?php

namespace App\Infrastructure\Controller\ShipTemplate;

use App\Application\ShipTemplate\MyTemplatesService;
use App\Application\ShipTemplate\Output\ListTemplatesOutput;
use App\Domain\TemplateAuthorId;
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

class MyTemplatesController
{
    public function __construct(
        private MyTemplatesService $myTemplatesService,
        private Security $security,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="ShipTemplate")
     * @OpenApi\Response(response=200, description="Get the collection of logged user's ship templates.", @Model(type=ListTemplatesOutput::class))
     */
    #[Route('/api/my-ship-templates', name: 'my_ship_templates', methods: ['GET'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->myTemplatesService->handle(TemplateAuthorId::fromString((string) $user->getId()));

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
