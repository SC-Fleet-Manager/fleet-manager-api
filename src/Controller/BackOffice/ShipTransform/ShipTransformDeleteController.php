<?php

namespace App\Controller\BackOffice\ShipTransform;

use App\Entity\ShipName;
use App\Repository\ShipNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShipTransformDeleteController extends AbstractController
{
    private ShipNameRepository $shipNameRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ShipNameRepository $shipNameRepository, EntityManagerInterface $entityManager)
    {
        $this->shipNameRepository = $shipNameRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/bo/ship-transform/delete/{id}", name="bo_ship_transform_delete", methods={"POST"})
     */
    public function __invoke(Request $request, string $id): Response
    {
        /** @var ShipName $shipName */
        $shipName = $this->shipNameRepository->find($id);
        if ($shipName === null) {
            return $this->redirectToRoute('bo_ship_transform_list');
        }

        $this->entityManager->remove($shipName);
        $this->entityManager->flush();

        return $this->redirectToRoute('bo_ship_transform_list');
    }
}
