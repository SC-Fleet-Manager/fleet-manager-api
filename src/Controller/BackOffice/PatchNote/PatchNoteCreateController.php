<?php

namespace App\Controller\BackOffice\PatchNote;

use App\Domain\PatchNoteId;
use App\Entity\PatchNote;
use App\Form\Dto\PatchNote as PatchNoteDto;
use App\Form\PatchNoteForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Ulid;

class PatchNoteCreateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route("/bo/patch-note/create", name: "bo_patch_note_create", methods: ["GET", "POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $patchNote = new PatchNoteDto();
        $form = $this->createForm(PatchNoteForm::class, $patchNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shipName = new PatchNote(
                new PatchNoteId(new Ulid()),
                $patchNote->title,
                $patchNote->body,
                $patchNote->link,
                new \DateTimeImmutable());
            $this->entityManager->persist($shipName);
            $this->entityManager->flush();

            return $this->redirectToRoute('bo_patch_note_list');
        }

        return $this->render('back_office/patch_note/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
