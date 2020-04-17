<?php

namespace App\Controller\BackOffice\PatchNote;

use App\Entity\PatchNote;
use App\Form\Dto\PatchNote as PatchNoteDto;
use App\Form\PatchNoteForm;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PatchNoteCreateController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/bo/patch-note/create", name="bo_patch_note_create", methods={"GET","POST"})
     */
    public function __invoke(Request $request): Response
    {
        $patchNote = new PatchNoteDto();
        $form = $this->createForm(PatchNoteForm::class, $patchNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shipName = new PatchNote(
                Uuid::uuid4(),
                $patchNote->title,
                $patchNote->body,
                $patchNote->link);
            $this->entityManager->persist($shipName);
            $this->entityManager->flush();

            return $this->redirectToRoute('bo_patch_note_list');
        }

        return $this->render('back_office/patch_note/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
