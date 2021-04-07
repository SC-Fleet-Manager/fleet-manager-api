<?php

namespace App\Infrastructure\Controller\PatchNote;

use App\Application\PatchNote\HasNewPatchNoteService;
use App\Application\PatchNote\Output\HasNewPatchNoteOutput;
use App\Entity\User;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class HasNewPatchNoteController
{
    public function __construct(
        private HasNewPatchNoteService $hasNewPatchNoteService,
        private Security $security,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="PatchNote")
     * @OpenApi\Get(description="Returns if the logged user has a new patch note to read or not.")
     * @OpenApi\Response(response=200, description="Ok.", @Model(type=HasNewPatchNoteOutput::class))
     */
    #[Route('/api/patch-note/has-new-patch-note', name: 'patch_note_has_new_patch_note', methods: ['GET'])]
    public function __invoke(): Response
    {
        if (!$this->security->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $output = $this->hasNewPatchNoteService->handle($user->getId());

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
