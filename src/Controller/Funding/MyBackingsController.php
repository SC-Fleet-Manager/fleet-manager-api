<?php

namespace App\Controller\Funding;

use App\Entity\User;
use App\Repository\FundingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MyBackingsController extends AbstractController
{
    public function __construct(
        private FundingRepository $fundingRepository
    ) {
    }

    #[Route("/api/funding/my-backings", name: "funding_my_backings", methods: ["GET"])]
    public function __invoke(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        /** @var User $user */
        $user = $this->getUser();

        $fundings = $this->fundingRepository->findBy(['user' => $user]);

        return $this->json($fundings, 200, [], ['groups' => 'my_backings']);
    }
}
