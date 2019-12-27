<?php

namespace App\Controller\BackOffice\ShipChassis;

use App\Repository\ShipChassisRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShipChassisListController extends AbstractController
{
    private ShipChassisRepository $shipChassisRepository;

    public function __construct(ShipChassisRepository $shipChassisRepository)
    {
        $this->shipChassisRepository = $shipChassisRepository;
    }

    /**
     * @Route("/bo/ship-chassis/list", name="bo_ship_chassis_list", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $shipChassis = $this->shipChassisRepository->findBy([], ['rsiId' => 'ASC']);

        return $this->render('back_office/ship_chassis/list.html.twig', [
            'ship_chassis' => $shipChassis,
        ]);
    }
}
