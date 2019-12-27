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
    private $security;
    private $validator;
    private $serializer;
    private $entityManager;
    private $userRepository;
    private $eventDispatcher;

    public function __construct(
        Security $security,
        ValidatorInterface $validator,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->security = $security;
        $this->validator = $validator;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->userRepository = $userRepository;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/api/profile/resolve-conflict-connect/discord", name="profile_resolve_conflict_connect", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
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
            $user->setUsername($alreadyLinkedUser->getNickname());
            $user->setNickname($alreadyLinkedUser->getNickname());
            if (!$user->getApiToken()) {
                $user->setApiToken(User::generateToken());
            }
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }

        return $this->json(null, 204);
    }
}
