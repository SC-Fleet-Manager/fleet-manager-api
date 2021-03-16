<?php

namespace App\Controller\PatchNote;

use App\Entity\PatchNote;
use App\Entity\User;
use App\Repository\PatchNoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class LastPatchNoteController extends AbstractController
{
    public function __construct(
        private Security $security,
        private PatchNoteRepository $patchNoteRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route("/api/last-patch-notes", name: "last_patch_notes", methods: ["GET"])]
    public function __invoke(): Response
    {
        /** @var PatchNote[] $patchNotes */
        $patchNotes = $this->patchNoteRepository->findBy([], ['createdAt' => 'DESC'], 5);

        $lastPatchNote = $patchNotes[0] ?? null;
        if ($this->security->isGranted('ROLE_USER') && $lastPatchNote !== null) {
            /** @var User $user */
            $user = $this->security->getUser();
            if ($user->getLastPatchNoteReadAt() === null
                || $lastPatchNote->getCreatedAt()->format('c') !== $user->getLastPatchNoteReadAt()->format('c')) {
                $user->setLastPatchNoteReadAt(clone $lastPatchNote->getCreatedAt());
                $this->entityManager->flush();
            }
        }

        return $this->json([
            'patchNotes' => $patchNotes,
        ]);
    }
}
