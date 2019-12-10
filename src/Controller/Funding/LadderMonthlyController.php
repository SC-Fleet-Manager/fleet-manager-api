<?php

namespace App\Controller\Funding;

use App\Service\Funding\LadderHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LadderMonthlyController extends AbstractController
{
    private LadderHandler $ladderHandler;

    public function __construct(LadderHandler $ladderHandler)
    {
        $this->ladderHandler = $ladderHandler;
    }

    /**
     * @Route("/api/funding/ladder-monthly", name="funding_ladder_monthly", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $topFundings = $this->ladderHandler->getMonthlyLadder();

        return $this->json([
            'topFundings' => $topFundings,
        ]);
    }
}
