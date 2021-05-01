<?php

namespace App\Infrastructure\Controller\MyFleet;

use App\Application\MyFleet\ImportFleetService;
use App\Entity\User;
use App\Infrastructure\Controller\MyFleet\Input\ImportFleetInput;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ImportFleetController
{
    public function __construct(
        private ImportFleetService $importFleetService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="MyFleet")
     * @OpenApi\RequestBody(
     *     @Model(type=ImportFleetInput::class)
     * )
     * @OpenApi\Response(response=204, description="Import ships from Hangar Transfer Format file.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/my-fleet/import', name: 'my_fleet_import', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var ImportFleetInput $input */
        $input = $this->serializer->deserialize($request->getContent(), ImportFleetInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->importFleetService->handle($user->getId(), $input->hangarExplorerContent, $input->onlyMissing);

        return new Response(null, 204);
    }
}
