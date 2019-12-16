<?php

namespace App\Controller\Profile;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class SavePreferencesController extends AbstractController
{
    private $security;
    private $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/profile/save-preferences", name="profile_save_preferences", methods={"POST"}, condition="request.getContentType() == 'json'")
     */
    public function __invoke(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

        /** @var User $user */
        $user = $this->security->getUser();
        $content = json_decode($request->getContent(), true);

        if (!isset($content['publicChoice'])) {
            return $this->json([
                'error' => 'invalid_form',
                'errorMessage' => 'The field publicChoice must not be blank.',
            ], 400);
        }
        $user->setPublicChoice($content['publicChoice']);

        $orgaVisibilityChoices = $content['orgaVisibilityChoices'] ?? [];
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }
        foreach ($orgaVisibilityChoices as $sid => $visibilityChoice) {
            $orga = $citizen->getOrgaBySid($sid);
            if ($orga === null) {
                continue;
            }
            $orga->setVisibility($visibilityChoice);
        }

        $this->entityManager->flush();

        return $this->json(null, 204);
    }
}
