<?php

namespace App\Infrastructure\Controller\Profile;

use App\Application\Profile\ChangeNicknameService;
use App\Entity\User;
use App\Infrastructure\Controller\Profile\Input\ChangeNicknameInput;
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

class ChangeNicknameController
{
    public function __construct(
        private ChangeNicknameService $changeNicknameService,
        private Security $security,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator
    ) {
    }

    /**
     * @OpenApi\Tag(name="Profile")
     * @OpenApi\Post(description="Changes or reset the nickname of the logged user.")
     * @OpenApi\RequestBody(
     *     @Model(type=ChangeNicknameInput::class)
     * )
     * @OpenApi\Response(response=204, description="Ok.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/profile/change-nickname', name: 'profile_change_nickname', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var ChangeNicknameInput $input */
        $input = $this->serializer->deserialize($request->getContent(), ChangeNicknameInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->changeNicknameService->handle($user->getId(), $input->nickname);

        return new JsonResponse(null, 204);
    }
}
