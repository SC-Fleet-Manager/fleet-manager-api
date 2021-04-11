<?php

namespace App\Infrastructure\Controller\Support;

use App\Application\Profile\Output\ProfileOutput;
use App\Application\Profile\ProfileService;
use App\Application\Support\GiveFeedbackService;
use App\Entity\User;
use App\Infrastructure\Controller\Profile\Input\SavePreferencesInput;
use App\Infrastructure\Controller\Support\Input\GiveFeedbackInput;
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

class GiveFeedbackController
{
    public function __construct(
        private GiveFeedbackService $giveFeedbackService,
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="Support")
     * @OpenApi\Post(description="Give feedback from the logged user.")
     * @OpenApi\RequestBody(
     *     @Model(type=GiveFeedbackInput::class)
     * )
     * @OpenApi\Response(response=204, description="Ok.")
     * @OpenApi\Response(response=400, description="Invalid payload.")
     */
    #[Route('/api/support/give-feedback', name: 'support_give_feedback', methods: ['POST'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var GiveFeedbackInput $input */
        $input = $this->serializer->deserialize($request->getContent(), GiveFeedbackInput::class, $request->getContentType());
        $this->validator->validate($input);

        /** @var User $user */
        $user = $this->security->getUser();

        $this->giveFeedbackService->handle($user->getId(), $user->getProfile(), $input->description, $input->email, $input->discordId);

        return new JsonResponse(null, 204);
    }
}
