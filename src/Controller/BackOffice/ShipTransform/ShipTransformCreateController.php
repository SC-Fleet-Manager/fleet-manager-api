<?php

namespace App\Controller\BackOffice\ShipTransform;

use App\Entity\ShipName;
use App\Form\Dto\ShipTransform;
use App\Form\ShipTransformForm;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShipTransformCreateController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/bo/ship-transform/create", name="bo_ship_transform_create", methods={"GET","POST"})
     */
    public function __invoke(Request $request): Response
    {
        $shipTransform = new ShipTransform();
        $form = $this->createForm(ShipTransformForm::class, $shipTransform);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shipName = new ShipName(Uuid::uuid4(), $shipTransform->myHangarName, $shipTransform->shipMatrixName);
            $this->entityManager->persist($shipName);
            $this->entityManager->flush();

            return $this->redirectToRoute('bo_ship_transform_list');
        }

        return $this->render('back_office/ship_transform/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
