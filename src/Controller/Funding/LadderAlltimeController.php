<?php

namespace App\Controller\Funding;

use App\Service\Funding\LadderHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LadderAlltimeController extends AbstractController
{
    private LadderHandler $ladderHandler;

    public function __construct(LadderHandler $ladderHandler)
    {
        $this->ladderHandler = $ladderHandler;
    }

    /**
     * @Route("/api/funding/ladder-alltime", name="funding_ladder_alltime", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $orgaMode = $request->query->getBoolean('orgaMode', false);

        $topFundings = $orgaMode ? $this->ladderHandler->getAlltimeOrgaLadder() : $this->ladderHandler->getAlltimeLadder();

        return $this->json([
            'topFundings' => $topFundings,
        ]);
    }
}
