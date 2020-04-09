<?php

namespace App\Controller\BackOffice\ShipTransform;

use App\Entity\ShipName;
use App\Form\Dto\ShipTransform;
use App\Form\ShipTransformForm;
use App\Repository\ShipNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ShipTransformEditController extends AbstractController
{
    private ShipNameRepository $shipNameRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(ShipNameRepository $shipNameRepository, EntityManagerInterface $entityManager)
    {
        $this->shipNameRepository = $shipNameRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/bo/ship-transform/edit/{id}", name="bo_ship_transform_edit", methods={"GET","POST"})
     */
    public function __invoke(Request $request, string $id): Response
    {
        /** @var ShipName $shipName */
        $shipName = $this->shipNameRepository->find($id);
        if ($shipName === null) {
            throw new NotFoundHttpException('Ship name not found.');
        }

        $shipTransform = new ShipTransform(
            $shipName->getMyHangarName(),
            $shipName->getShipMatrixName(),
            $shipName->getProviderId() !== null ? $shipName->getProviderId()->toString() : null,
            $shipName->getMyHangarNamePattern());
        $form = $this->createForm(ShipTransformForm::class, $shipTransform);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $shipName
                ->setMyHangarName($shipTransform->myHangarName)
                ->setShipMatrixName($shipTransform->shipMatrixName)
                ->setProviderId(Uuid::fromString($shipTransform->providerId))
                ->setMyHangarNamePattern($shipTransform->myHangarNamePattern);
            $this->entityManager->flush();

            return $this->redirectToRoute('bo_ship_transform_list');
        }

        return $this->render('back_office/ship_transform/edit.html.twig', [
            'shipName' => $shipName,
            'form' => $form->createView(),
        ]);
    }
}
