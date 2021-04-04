<?php

namespace App\Infrastructure\Controller\PatchNote;

use App\Application\PatchNote\LastPatchNotesService;
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

    #[Route("/api/last-patch-notes", name: "last_patch_notes", methods: ["GET"])]
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
