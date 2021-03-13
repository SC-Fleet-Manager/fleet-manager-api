<?php

namespace App\Controller\Profile;

use App\Entity\Funding;
use App\Entity\User;
use App\Event\CitizenDeletedEvent;
use App\Form\Dto\ResolveConflictConnect;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ResolveConflictConnectController extends AbstractController
{
    public function __construct(
        private Security $security,
        private ValidatorInterface $validator,
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route("/api/profile/resolve-conflict-connect/discord", name: "profile_resolve_conflict_connect", methods: ["POST"])]
    public function __invoke(
        Request $request
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        /** @var User $alreadyLinkedUser */
        $alreadyLinkedUser = $this->userRepository->findOneBy(['discordId' => $user->getPendingDiscordId()]);
        if ($alreadyLinkedUser === null || $alreadyLinkedUser->getId()->equals($user->getId())) {
            return $this->json([
                'error' => 'no_linked_user',
                'errorMessage' => 'There is no other users with your Discord account.',
            ], 400);
        }
        if ($user->getCitizen() === null || $alreadyLinkedUser->getCitizen() === null) {
            return $this->json([
                'error' => 'at_least_null_citizen',
                'errorMessage' => 'There is no more conflict to link your Discord account. Please click on "Link my Discord" to try again.',
            ], 400);
        }

        /** @var ResolveConflictConnect $resolveConflictConnect */
        $resolveConflictConnect = $this->serializer->denormalize($request->request->all(), ResolveConflictConnect::class);
        $resolveConflictConnect->setConflictChoices([
            $user->getCitizen()->getId()->toString(),
            $alreadyLinkedUser->getCitizen()->getId()->toString(),
        ]);
        $errors = $this->validator->validate($resolveConflictConnect);

        if ($errors->count() > 0) {
            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        $this->entityManager->beginTransaction();
        try {
            $citizenToKeep = $user->getCitizen();
            $citizenToRemove = $alreadyLinkedUser->getCitizen();
            if ($resolveConflictConnect->conflictChoice === $alreadyLinkedUser->getCitizen()->getId()->toString()) {
                $citizenToKeep = $alreadyLinkedUser->getCitizen();
                $citizenToRemove = $user->getCitizen();
            }

            // TODO : if transaction rollbacks, the event is inconsistent : set citizen + its organizations in Event and dispatch it after commit()
            $this->eventDispatcher->dispatch(new CitizenDeletedEvent($citizenToRemove));

            $this->entityManager->remove($citizenToRemove);
            $fundings = $this->entityManager->getRepository(Funding::class)->findBy(['user' => $alreadyLinkedUser]);
            foreach ($fundings as $funding) {
                $this->entityManager->remove($funding);
            }
            $this->entityManager->remove($alreadyLinkedUser);
            $this->entityManager->flush();
            $user->setCitizen($citizenToKeep);
            if ($alreadyLinkedUser->getCreatedAt() < $user->getCreatedAt()) {
                $user->setCreatedAt(clone $alreadyLinkedUser->getCreatedAt());
            }
            $user->setPendingDiscordId(null);
            $user->setDiscordId($alreadyLinkedUser->getDiscordId());
            $user->setDiscordTag($alreadyLinkedUser->getDiscordTag());
            $user->setNickname($alreadyLinkedUser->getNickname());
            if (!$user->getApiToken()) {
                $user->setApiToken(User::generateToken());
            }
            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $this->json(null, 204);
    }
}
