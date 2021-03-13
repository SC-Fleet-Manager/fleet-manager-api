<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Event\CitizenDeletedEvent;
use App\Repository\FundingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DeleteAccountController extends AbstractController
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
        private FundingRepository $fundingRepository
    ) {
    }

    #[Route("/api/profile/delete-account", name: "profile_delete_account", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen !== null) {
            $this->entityManager->remove($citizen);
            // TODO : if transaction rollbacks, the event is inconsistent : set citizen + its organizations in Event and dispatch it after commit()
            $this->eventDispatcher->dispatch(new CitizenDeletedEvent($citizen));
        }
        $fundings = $this->fundingRepository->findBy(['user' => $user]);
        foreach ($fundings as $funding) {
            $funding->setUser(null);
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
