<?php

namespace App\Infrastructure\Controller\Profile;

use App\Application\Profile\Output\ProfileOutput;
use App\Application\Profile\ProfileService;
use App\Application\Repository\UserRepositoryInterface;
use App\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        private ProfileService $profileService,
        private Security $security,
        private UserRepositoryInterface $userRepository,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="Profile")
     * @OpenApi\Get(description="Retrieve the detailed profile of the logged user.")
     * @OpenApi\Response(response=200, description="Ok.", @Model(type=ProfileOutput::class))
     */
    #[Route('/api/profile', name: 'profile', methods: ['GET'])]
    public function __invoke(
        Request $request
    ): Response {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->profileService->handle($user->getId());

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
