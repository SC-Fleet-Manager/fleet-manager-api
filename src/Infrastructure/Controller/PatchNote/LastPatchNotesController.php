<?php

namespace App\Infrastructure\Controller\PatchNote;

use App\Application\PatchNote\LastPatchNotesService;
use App\Application\PatchNote\Output\LastPatchNotesOutput;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class LastPatchNotesController
{
    public function __construct(
        private LastPatchNotesService $lastPatchNotesService,
        private Security $security,
        private SerializerInterface $serializer,
    ) {
    }

    /**
     * @OpenApi\Tag(name="PatchNote")
     * @OpenApi\Get(description="Retrieve the last 10 patch notes. If a user is logged, its last patch note date will be updated (cf. /api/patch-note/has-new-patch-note).")
     * @OpenApi\Response(response=200, description="Ok.", @Model(type=LastPatchNotesOutput::class))
     */
    #[Route('/api/patch-note/last-patch-notes', name: 'patch_note_last_patch_notes', methods: ['GET'])]
    public function __invoke(): Response
    {
        $userId = $this->security->isGranted('ROLE_USER')
            ? $this->security->getUser()->getId()
            : null;

        $output = $this->lastPatchNotesService->handle($userId);

        $json = $this->serializer->serialize($output, 'json');

        return new JsonResponse($json, 200, [], true);
    }
}
