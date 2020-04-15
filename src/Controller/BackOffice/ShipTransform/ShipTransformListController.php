<?php

namespace App\Controller\BackOffice\ShipTransform;

use App\Repository\ShipNameRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShipTransformListController extends AbstractController
{
    private ShipNameRepository $shipNameRepository;

    public function __construct(ShipNameRepository $shipNameRepository)
    {
        $this->shipNameRepository = $shipNameRepository;
    }

    /**
     * @Route("/bo/ship-transform/list", name="bo_ship_transform_list", methods={"GET"})
     */
    public function __invoke(Request $request): Response
    {
        $shipNames = $this->shipNameRepository->findBy([], ['myHangarNamePattern' => 'ASC']);

        return $this->render('back_office/ship_transform/list.html.twig', [
            'ship_names' => $shipNames,
        ]);
    }
}
