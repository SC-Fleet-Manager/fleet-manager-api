<?php

namespace App\Controller\BackOffice\ShipChassis;

use App\Entity\ShipChassis;
use App\Repository\ShipChassisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShipChassisDeleteController extends AbstractController
{
    private ShipChassisRepository $shipChassisRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ShipChassisRepository $shipChassisRepository, EntityManagerInterface $entityManager)
    {
        $this->shipChassisRepository = $shipChassisRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/bo/ship-chassis/delete/{id}", name="bo_ship_chassis_delete", methods={"POST"})
     */
    public function __invoke(Request $request, string $id): Response
    {
        /** @var ShipChassis $shipChassis */
        $shipChassis = $this->shipChassisRepository->find($id);
        if ($shipChassis === null) {
            return $this->redirectToRoute('bo_ship_chassis_list');
        }

        $this->entityManager->remove($shipChassis);
        $this->entityManager->flush();

        return $this->redirectToRoute('bo_ship_chassis_list');
    }
}
