<?php

namespace App\Infrastructure\Controller;

use App\Domain\Citizen;
use App\Domain\CitizenInfos;
use App\Domain\CitizenInfosProviderInterface;
use App\Domain\CitizenRepositoryInterface;
use App\Domain\Exception\NotFoundHandleSCException;
use App\Domain\HandleSC;
use App\Domain\User;
use App\Domain\UserRepositoryInterface;
use App\Infrastructure\Form\Dto\LinkAccount;
use App\Infrastructure\Form\Dto\UpdateHandle;
use App\Infrastructure\Form\LinkAccountForm;
use App\Infrastructure\Form\UpdateHandleForm;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProfileController extends AbstractController
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var CitizenInfosProviderInterface
     */
    private $citizenInfosProvider;

    /**
     * @var CitizenRepositoryInterface
     */
    private $citizenRepository;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var Security
     */
    private $security;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    public function __construct(
        LoggerInterface $logger,
        TranslatorInterface $translator,
        CitizenInfosProviderInterface $citizenInfosProvider,
        CitizenRepositoryInterface $citizenRepository,
        UserRepositoryInterface $userRepository,
        Security $security,
        FormFactoryInterface $formFactory)
    {
        $this->logger = $logger;
        $this->translator = $translator;
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->citizenRepository = $citizenRepository;
        $this->userRepository = $userRepository;
        $this->security = $security;
        $this->formFactory = $formFactory;
    }

    /**
     * @Route("/profile", name="profile", methods={"GET"})
     *
     * Retrieves profile infos : user properties.
     */
    public function index(Request $request, Security $security): Response
    {
        /** @var User $user */
        $user = $security->getUser();

        return $this->json($user, 200, [], ['groups' => 'profile']);
    }

    /**
     * @Route("/update-handle", name="update_handle", methods={"POST"})
     *
     * Update the SC Handle of logged User. Compare the current and target Citizen numbers.
     */
    public function updateHandle(Request $request): Response
    {
        $updateHandle = new UpdateHandle();
        $form = $this->formFactory->createNamedBuilder('', UpdateHandleForm::class, $updateHandle)->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'error' => 'not_submitted_form',
                'errorMessage' => 'No data has been submitted.',
            ], 400);
        }
        if (!$form->isValid()) {
            $formErrors = $form->getErrors(true);
            $errors = [];
            foreach ($formErrors as $formError) {
                $errors[] = $formError->getMessage();
            }

            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        try {
            $citizenInfos = $this->citizenInfosProvider->retrieveInfos(new HandleSC($updateHandle->handleSC));
            if (!$citizenInfos->numberSC->equals($user->citizen->number)) {
                return $this->json([
                    'error' => 'invalid_form',
                    'formErrors' => ['This SC handle does not have the same SC number than yours.'],
                ], 400);
            }

            $user->citizen->actualHandle = new HandleSC($updateHandle->handleSC);
            $this->citizenRepository->update($user->citizen);
        } catch (NotFoundHandleSCException $e) {
            return $this->json([
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The SC handle %s does not exist.', $updateHandle->handleSC),
            ], 400);
        }

        return $this->json(null, 204);
    }

    /**
     * @Route("/link-account", name="link_account", methods={"POST"})
     *
     * Link RSI Account (with SC Handle and Bio token) with the actual logged User.
     */
    public function linkAccount(Request $request): Response
    {
        $linkAccount = new LinkAccount();
        $form = $this->formFactory->createNamedBuilder('', LinkAccountForm::class, $linkAccount)->getForm();
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'error' => 'not_submitted_form',
                'errorMessage' => 'No data has been submitted.',
            ], 400);
        }
        if (!$form->isValid()) {
            $formErrors = $form->getErrors(true);
            $errors = [];
            foreach ($formErrors as $formError) {
                $errors[] = $formError->getMessage();
            }

            return $this->json([
                'error' => 'invalid_form',
                'formErrors' => $errors,
            ], 400);
        }

        /** @var User $user */
        $user = $this->security->getUser();

        try {
            $citizenInfos = $this->citizenInfosProvider->retrieveInfos(new HandleSC($linkAccount->handleSC));
            if (!$this->isTokenValid($user, $citizenInfos)) {
                return $this->json([
                    'error' => 'invalid_form',
                    'formErrors' => ['Your RSI bio does not contain this token.'],
                ], 400);
            }

            $this->attachCitizenToUser($user, $citizenInfos);
        } catch (NotFoundHandleSCException $e) {
            return $this->json([
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The SC handle %s does not exist.', $linkAccount->handleSC),
            ], 400);
        }

        return $this->json(null, 204);
    }

    private function attachCitizenToUser(User $user, CitizenInfos $citizenInfos): void
    {
        // add citizen to user
        $citizen = $this->citizenRepository->getByHandle($citizenInfos->handle);
        $isNew = $citizen === null;
        if ($isNew) {
            // create new citizen
            $citizen = new Citizen(Uuid::uuid4());
        }

        $citizen->number = clone $citizenInfos->numberSC;
        $citizen->actualHandle = clone $citizenInfos->handle;
        $citizen->bio = $citizenInfos->bio;
        $citizen->organisations = [];
        foreach ($citizenInfos->organisations as $organisation) {
            $citizen->organisations[] = clone $organisation;
        }

        if ($isNew) {
            $this->citizenRepository->create($citizen);
        } else {
            $this->citizenRepository->update($citizen);
        }

        $user->citizen = $citizen;
        $this->userRepository->update($user);
    }

    private function isTokenValid(User $user, CitizenInfos $citizenInfos): bool
    {
        return stripos($citizenInfos->bio, $user->token) !== false;
    }
}
