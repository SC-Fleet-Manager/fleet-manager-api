<?php

namespace App\Controller\BackOffice\ShipTransform;

use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RefreshShipInfosController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ShipInfosProviderInterface $shipInfosProvider;

    public function __construct(ShipInfosProviderInterface $shipInfosProvider)
    {
        $this->shipInfosProvider = $shipInfosProvider;
    }

    /**
     * @Route("/bo/refresh-ship-infos", name="bo_refresh_ship_infos", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        try {
            $this->shipInfosProvider->refreshShips();
        } catch (\Exception $e) {
            $this->logger->error('[RefreshShipInfos] unable to refresh ships.', ['exception' => $e]);
            $this->addFlash('danger', 'An error has occurred. Unable to refresh ships.');
        }

        return $this->redirectToRoute('bo_ship_transform_list');
    }
}
