<?php

namespace App\Controller;

use App\Domain\HandleSC;
use App\Entity\Organization;
use App\Entity\User;
use App\Exception\NotFoundHandleSCException;
use App\Form\Dto\UpdateHandle;
use App\Form\UpdateHandleForm;
use App\Repository\CitizenOrganizationRepository;
use App\Repository\OrganizationRepository;
use App\Service\CitizenInfosProviderInterface;
use App\Service\CitizenRefresher;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/profile", name="profile_")
 */
class ProfileController extends AbstractController
{
    private $citizenInfosProvider;
    private $security;
    private $formFactory;
    private $entityManager;
    private $citizenRefresher;

    public function __construct(
        CitizenInfosProviderInterface $citizenInfosProvider,
        EntityManagerInterface $entityManager,
        Security $security,
        FormFactoryInterface $formFactory,
        CitizenRefresher $citizenRefresher
    ) {
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->citizenRefresher = $citizenRefresher;
    }

    /**
     * @Route("/", name="index", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     *
     * Retrieves profile infos : user properties.
     */
    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        return $this->json($user, 200, [], ['groups' => 'profile']);
    }

    /**
     * @Route("/refresh-rsi-profile", name="refresh_rsi_profile", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function refreshRsiProfile(): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen',
                'errorMessage' => 'No citizens are linked to your account.',
            ], 400);
        }
        if (!$citizen->canBeRefreshed()) {
            return $this->json([
                'error' => 'too_many_refresh',
                'errorMessage' => sprintf('Please wait %d minutes before refreshing.', $citizen->getTimeLeftBeforeRefreshing()->format('%i')),
            ], 400);
        }

        $citizenInfos = $this->citizenInfosProvider->retrieveInfos(clone $citizen->getActualHandle());
        if (!$citizenInfos->numberSC->equals($citizen->getNumber())) {
            return $this->json([
                'error' => 'bad_citizen',
                'errorMessage' => sprintf('Your SC handle has probably changed. Please update it in <a href="/profile/">your Profile</a>.'),
            ], 400);
        }

        $this->citizenRefresher->refreshCitizen($citizen, $citizenInfos);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @Route("/save-preferences", name="save_preferences", methods={"POST"}, condition="request.getContentType() == 'json'")
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function savePreferences(Request $request, CitizenOrganizationRepository $citizenOrganizationRepository, OrganizationRepository $organizationRepository): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $content = \json_decode($request->getContent(), true);

        $user->setPublicChoice($content['publicChoice'] ?? User::PUBLIC_CHOICE_PRIVATE);
        $this->entityManager->flush();

        $orgaPublicChoices = $content['orgaPublicChoices'] ?? [];
        $citizen = $user->getCitizen();
        if ($citizen !== null) {
            foreach ($orgaPublicChoices as $sid => $publicChoice) {
                $orga = $citizen->getOrgaBySid($sid);
                if ($orga === null) {
                    continue;
                }
                if (!in_array($publicChoice, [Organization::PUBLIC_CHOICE_PRIVATE, Organization::PUBLIC_CHOICE_PUBLIC], true)) {
                    continue;
                }
                $citizenOrgas = $citizenOrganizationRepository->findGreaterThanRank($sid, $orga->getRank());
                if (count($citizenOrgas) === 0) {
                    // granted to manage $citizenOrga settings
                    $organizationRepository->updatePublicChoice($sid, $publicChoice);
                }
            }
        }

        return $this->json(null, 204);
    }

    /**
     * @Route("/update-handle", name="update_handle", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     *
     * Update the SC Handle of logged User. Compare the current and target Citizen numbers.
     */
    public function updateHandle(Request $request): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen',
                'errorMessage' => 'Account not linked yet.',
            ], 400);
        }

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

        try {
            $citizenInfos = $this->citizenInfosProvider->retrieveInfos(new HandleSC($updateHandle->handleSC), false);
            if (!$citizenInfos->numberSC->equals($citizen->getNumber())) {
                return $this->json([
                    'error' => 'invalid_form',
                    'formErrors' => ['This SC handle does not have the same SC number than yours.'],
                ], 400);
            }
            $citizen->setActualHandle(new HandleSC($updateHandle->handleSC));
            $this->citizenRefresher->refreshCitizen($citizen, $citizenInfos);
            $this->entityManager->flush();
        } catch (NotFoundHandleSCException $e) {
            return $this->json([
                'error' => 'not_found_handle',
                'errorMessage' => sprintf('The SC handle %s does not exist.', $updateHandle->handleSC),
            ], 400);
        }

        return $this->json(null, 204);
    }
}
