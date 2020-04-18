<?php

namespace App\Controller\PatchNote;

use App\Entity\User;
use App\Repository\PatchNoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class HasNewPatchNoteController extends AbstractController
{
    private Security $security;
    private PatchNoteRepository $patchNoteRepository;

    public function __construct(Security $security, PatchNoteRepository $patchNoteRepository)
    {
        $this->security = $security;
        $this->patchNoteRepository = $patchNoteRepository;
    }

    /**
     * @Route("/api/has-new-patch-note", name="has_new_patch_note", methods={"GET"})
     */
    public function __invoke(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();

        $oneRecentPatchNoteId = $this->patchNoteRepository->findOneRecentPatchNoteId($user->getLastPatchNoteReadAt());

        return $this->json([
            'hasNewPatchNote' => $oneRecentPatchNoteId !== null,
        ]);
    }
}
