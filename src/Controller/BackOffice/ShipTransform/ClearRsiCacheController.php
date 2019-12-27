<?php

namespace App\Controller\BackOffice\ShipTransform;

use App\Service\Ship\InfosProvider\ApiShipInfosProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;

class ClearRsiCacheController extends AbstractController
{
    private CacheInterface $cache;
    private ApiShipInfosProvider $shipInfosProvider;

    public function __construct(CacheInterface $rsiShipsCache, ApiShipInfosProvider $shipInfosProvider)
    {
        $this->cache = $rsiShipsCache;
        $this->shipInfosProvider = $shipInfosProvider;
    }

    /**
     * @Route("/bo/clear-rsi-cache", name="bo_clear_rsi_cache", methods={"POST"})
     */
    public function __invoke(Request $request): Response
    {
        try {
            $this->cache->delete('ship_matrix');
        } catch (\Exception $e) {
            // pass
        }
        $this->shipInfosProvider->getAllShips(); // warmup

        return $this->redirectToRoute('bo_ship_transform_list');
    }
}
