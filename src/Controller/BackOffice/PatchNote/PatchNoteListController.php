<?php

namespace App\Controller\BackOffice\PatchNote;

use App\Repository\PatchNoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PatchNoteListController extends AbstractController
{
    public function __construct(
        private PatchNoteRepository $patchNoteRepository
    ) {
    }

    #[Route("/bo/patch-note/list", name: "bo_patch_note_list", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        $patchNotes = $this->patchNoteRepository->findBy([], ['createdAt' => 'DESC']);

        return $this->render('back_office/patch_note/list.html.twig', [
            'patch_notes' => $patchNotes,
        ]);
    }
}
