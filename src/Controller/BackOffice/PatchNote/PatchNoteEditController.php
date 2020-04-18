<?php

namespace App\Controller\BackOffice\PatchNote;

use App\Entity\PatchNote;
use App\Form\Dto\PatchNote as PatchNoteDto;
use App\Form\PatchNoteForm;
use App\Repository\PatchNoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class PatchNoteEditController extends AbstractController
{
    private PatchNoteRepository $patchNoteRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(PatchNoteRepository $patchNoteRepository, EntityManagerInterface $entityManager)
    {
        $this->patchNoteRepository = $patchNoteRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/bo/patch-note/edit/{id}", name="bo_patch_note_edit", methods={"GET","POST"})
     */
    public function __invoke(Request $request, string $id): Response
    {
        /** @var PatchNote $patchNote */
        $patchNote = $this->patchNoteRepository->find($id);
        if ($patchNote === null) {
            throw new NotFoundHttpException('Patch Note not found.');
        }

        $patchnotedto = new PatchNoteDto(
            $patchNote->getTitle(),
            $patchNote->getBody(),
            $patchNote->getLink());
        $form = $this->createForm(PatchNoteForm::class, $patchnotedto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $patchNote
                ->setTitle($patchnotedto->title)
                ->setBody($patchnotedto->body)
                ->setLink($patchnotedto->link);
            $this->entityManager->flush();

            return $this->redirectToRoute('bo_patch_note_list');
        }

        return $this->render('back_office/patch_note/edit.html.twig', [
            'patch_note' => $patchNote,
            'form' => $form->createView(),
        ]);
    }
}
