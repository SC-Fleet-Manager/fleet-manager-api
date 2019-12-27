<?php

namespace App\Controller\BackOffice\ShipChassis;

use App\Entity\ShipChassis;
use App\Form\Dto\ShipChassis as ShipChassisDto;
use App\Form\ShipChassisForm;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShipChassisCreateController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/bo/ship-chassis/create", name="bo_ship_chassis_create", methods={"GET","POST"})
     */
    public function __invoke(Request $request): Response
    {
        $shipChassisDto = new ShipChassisDto();
        $form = $this->createForm(ShipChassisForm::class, $shipChassisDto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shipChassis = new ShipChassis(Uuid::uuid4(), $shipChassisDto->rsiId, $shipChassisDto->name);
            $this->entityManager->persist($shipChassis);
            $this->entityManager->flush();

            return $this->redirectToRoute('bo_ship_chassis_list');
        }

        return $this->render('back_office/ship_chassis/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
