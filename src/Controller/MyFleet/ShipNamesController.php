<?php

namespace App\Controller\MyFleet;

use App\Repository\ShipNameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShipNamesController extends AbstractController
{
    private ShipNameRepository $shipNameRepository;

    public function __construct(ShipNameRepository $shipNameRepository)
    {
        $this->shipNameRepository = $shipNameRepository;
    }

    /**
     * @Route("/api/ship-names", name="ship_names", methods={"GET"})
     */
    public function __invoke(): Response
    {
        $shipNames = $this->shipNameRepository->findAllShipNames();

        return $this->json([
            'shipNames' => $shipNames,
        ]);
    }
}
