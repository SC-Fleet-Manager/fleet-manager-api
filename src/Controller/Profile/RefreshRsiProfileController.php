<?php

namespace App\Controller\Profile;

use App\Entity\User;
use App\Service\CitizenInfosProviderInterface;
use App\Service\CitizenRefresher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class RefreshRsiProfileController extends AbstractController
{
    private $security;
    private $citizenInfosProvider;
    private $citizenRefresher;
    private $entityManager;

    public function __construct(
        Security $security,
        CitizenInfosProviderInterface $citizenInfosProvider,
        CitizenRefresher $citizenRefresher,
        EntityManagerInterface $entityManager
    ) {
        $this->security = $security;
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->citizenRefresher = $citizenRefresher;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/profile/refresh-rsi-profile", name="profile_refresh_rsi_profile", methods={"POST"})
     */
    public function __invoke(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

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
}
