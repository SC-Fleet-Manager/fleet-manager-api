<?php

namespace App\Infrastructure\Controller\Profile;

use App\Application\Profile\ChangeHandleService;
use App\Entity\User;
use App\Infrastructure\Controller\Profile\Input\ChangeHandleInput;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ChangeHandleController
{
    public function __construct(
        private ChangeHandleService $changeHandleService,
        private Security $security,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * @OpenApi\Tag(name="Profile")
     * @OpenApi\Post(description="Changes the handle of the logged user.")
     * @OpenApi\RequestBody(
     *     @Model(type=ChangeHandleInput::class)
     * )
     * @OpenApi\Response(response=204, description="Ok.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/profile/change-handle', name: 'profile_change_handle', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        /** @var ChangeHandleInput $input */
        $input = $this->serializer->deserialize($request->getContent(), ChangeHandleInput::class, $request->getContentType());
        $input->userId = $user->getId();
        $this->validator->validate($input);

        $this->changeHandleService->handle($user->getId(), $input->handle);

        return new JsonResponse(null, 204);
    }
}
