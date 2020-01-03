<?php

namespace App\Controller\Profile;

use App\Domain\HandleSC;
use App\Entity\User;
use App\Exception\NotFoundHandleSCException;
use App\Form\Dto\UpdateHandle;
use App\Form\UpdateHandleForm;
use App\Service\Citizen\CitizenRefresher;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class UpdateHandleController extends AbstractController
{
    private Security $security;
    private FormFactoryInterface $formFactory;
    private CitizenInfosProviderInterface $citizenInfosProvider;
    private CitizenRefresher $citizenRefresher;
    private EntityManagerInterface $entityManager;

    public function __construct(
        Security $security,
        FormFactoryInterface $formFactory,
        CitizenInfosProviderInterface $citizenInfosProvider,
        CitizenRefresher $citizenRefresher,
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->formFactory = $formFactory;
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->citizenRefresher = $citizenRefresher;
        $this->entityManager = $entityManager;
    }

    /**
     * Update the SC Handle of logged User. Compare the current and target Citizen numbers.
     *
     * @Route("/api/profile/update-handle", name="profile_update_handle", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

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
                'errorMessage' => sprintf('Sorry, the handle %s does not exist.', (string) $e->handleSC),
            ], 400);
        }

        return $this->json(null, 204);
    }
}
