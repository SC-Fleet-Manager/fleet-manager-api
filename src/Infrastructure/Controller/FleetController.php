<?php

namespace App\Infrastructure\Controller;

use App\Domain\FleetRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FleetController extends AbstractController
{
    /**
     * @Route("/fleets", name="fleets", options={"expose":true})
     */
    public function fleets(FleetRepositoryInterface $fleetRepository): Response
    {
        $fleets = $fleetRepository->all();
        // TODO : JMS or SF Serializer ?
        return $this->json($fleets);
    }
}
