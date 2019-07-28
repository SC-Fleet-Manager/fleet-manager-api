<?php

namespace App\Controller\Profile;

use App\Domain\CitizenInfos;
use App\Domain\HandleSC;
use App\Entity\Citizen;
use App\Entity\User;
use App\Exception\NotFoundHandleSCException;
use App\Form\Dto\LinkAccount;
use App\Form\LinkAccountForm;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use App\Service\Citizen\CitizenRefresher;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

class LinkAccountController extends AbstractController
{
    private $formFactory;
    private $security;
    private $serializer;
    private $entityManager;
    private $citizenRepository;
    private $userRepository;
    private $citizenRefresher;
    private $citizenInfosProvider;
    private $profileLinkAccountLogger;

    public function __construct(
        FormFactoryInterface $formFactory,
        Security $security,
        SerializerInterface $serializer,
        EntityManagerInterface $entityManager,
        CitizenRefresher $citizenRefresher,
        CitizenInfosProviderInterface $citizenInfosProvider,
        LoggerInterface $profileLinkAccountLogger
    ) {
        $this->formFactory = $formFactory;
        $this->security = $security;
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->citizenRepository = $entityManager->getRepository(Citizen::class);
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->citizenRefresher = $citizenRefresher;
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->profileLinkAccountLogger = $profileLinkAccountLogger;
    }

    /**
     * Link RSI Account (with SC Handle and Bio token) with the actual logged User.
     *
     * @Route("/api/profile/link-account", name="profile_link_account", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        $linkAccount = new LinkAccount();
        $form = $this->formFactory->createNamedBuilder('', LinkAccountForm::class, $linkAccount)->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'error' => 'not_submitted_form',
                'errorMessage' => 'No data has been submitted.',
            ], 400);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        $this->profileLinkAccountLogger->info('Link account submitted.',
            ['handleSC' => $linkAccount->handleSC, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);

        if (!$form->isValid()) {
            $formErrors = $form->getErrors(true);
            $errors = [];
            foreach ($formErrors as $formError) {
                $errors[] = $formError->getMessage();
            }

            $this->profileLinkAccountLogger->error('Invalid form.',
                ['errors' => $errors, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);

            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        try {
            $citizenInfos = $this->citizenInfosProvider->retrieveInfos(new HandleSC($linkAccount->handleSC), false);

            $this->profileLinkAccountLogger->info('Retrieve citizens infos.',
                ['infos' => $this->serializer->serialize($citizenInfos, 'json'), 'handleSC' => $linkAccount->handleSC, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);

            if (!$this->isTokenValid($user, $citizenInfos)) {
                $this->profileLinkAccountLogger->error('Token not valid.',
                    ['infos' => $this->serializer->serialize($citizenInfos, 'json'), 'handleSC' => $linkAccount->handleSC, 'userId' => $user->getId(), 'nickname' => $user->getNickname(), 'userToken' => $user->getToken()]);

                return $this->json([
                    'error' => 'invalid_form',
                    'formErrors' => ['Sorry, your RSI bio does not contain this token. Please copy-paste the following token to your RSI short bio.'],
                ], 400);
            }
            $this->attachCitizenToUser($user, $citizenInfos);
        } catch (NotFoundHandleSCException $e) {
            $this->profileLinkAccountLogger->error('Citizen infos not found.',
                ['exception' => $e, 'handleSC' => $linkAccount->handleSC, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);

            return $this->json([
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The SC handle %s does not exist. Try to check the typo.', $linkAccount->handleSC),
            ], 400);
        }

        $this->profileLinkAccountLogger->info('Link success.',
            ['handleSC' => $linkAccount->handleSC, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);

        return $this->json(null, 204);
    }

    private function attachCitizenToUser(User $user, CitizenInfos $citizenInfos): void
    {
        /** @var Citizen|null $citizen */
        $citizen = $this->citizenRepository->findOneBy(['actualHandle' => $citizenInfos->handle]);

        $isNew = $citizen === null;
        if ($isNew) {
            $this->profileLinkAccountLogger->info('New citizen.',
                ['infos' => $citizenInfos, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);
            $citizen = new Citizen(Uuid::uuid4());
        } else {
            $this->profileLinkAccountLogger->warning('Existing citizen.',
                ['citizenId' => $citizen->getId(), 'citizenHandle' => $citizen->getActualHandle()->getHandle(), 'infos' => $citizenInfos, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);

            /** @var User|null $userWithThatCitizen */
            $userWithThatCitizen = $this->userRepository->findOneBy(['citizen' => $citizen]);

            if ($userWithThatCitizen !== null) {
                $this->profileLinkAccountLogger->warning('Actual user that has this citizen found.', [
                    'citizenId' => $citizen->getId(),
                    'citizenHandle' => $citizen->getActualHandle(),
                    'infos' => $citizenInfos,
                    'userId' => $user->getId(),
                    'nickname' => $user->getNickname(),
                    'oldUserId' => $userWithThatCitizen->getId(),
                    'oldUserNickname' => $userWithThatCitizen->getNickname(),
                ]);
                $userWithThatCitizen->setCitizen(null); // detach for old user
                $this->entityManager->flush();
            } else {
                $this->profileLinkAccountLogger->warning('No actual user that has this citizen found.',
                    ['citizenId' => $citizen->getId(), 'citizenHandle' => $citizen->getActualHandle()->getHandle(), 'infos' => $citizenInfos, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);
            }
        }

        $user->setCitizen($citizen);
        $citizen
            ->setNumber(clone $citizenInfos->numberSC)
            ->setActualHandle(clone $citizenInfos->handle);
        if ($isNew) {
            $this->entityManager->persist($citizen);
            $this->entityManager->flush();
        }
        $this->citizenRefresher->refreshCitizen($citizen, $citizenInfos);

        $this->profileLinkAccountLogger->info('Set citizen to user.',
            ['citizenId' => $citizen->getId(), 'citizenHandle' => $citizen->getActualHandle()->getHandle(), 'infos' => $citizenInfos, 'userId' => $user->getId(), 'nickname' => $user->getNickname()]);

        $this->entityManager->flush();
    }

    private function isTokenValid(User $user, CitizenInfos $citizenInfos): bool
    {
        return stripos($citizenInfos->bio, $user->getToken()) !== false;
    }
}
