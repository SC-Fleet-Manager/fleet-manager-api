<?php

namespace App\Controller\Funding;

use App\Service\Funding\LadderHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LadderMonthlyController extends AbstractController
{
    public function __construct(
        private LadderHandler $ladderHandler
    ) {
    }

    #[Route("/api/funding/ladder-monthly", name: "funding_ladder_monthly", methods: ["GET"])]
    public function __invoke(
        Request $request
    ): Response {
        $orgaMode = $request->query->getBoolean('orgaMode', false);

        $topFundings = $orgaMode ? $this->ladderHandler->getMonthlyOrgaLadder() : $this->ladderHandler->getMonthlyLadder();

        return $this->json([
            'topFundings' => $topFundings,
        ]);
    }
}
