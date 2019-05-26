<?php

namespace App\Service;

use App\Domain\ShipInfo;

class FakeShipInfosProvider extends ApiShipInfosProvider
{
    public function getAllShips(): iterable
    {
        $shipInfos = [];

        // Cutlass Black
        $shipInfo = new ShipInfo();
        $shipInfo->id = '56';
        $shipInfo->productionStatus = ShipInfo::FLIGHT_READY;
        $shipInfo->minCrew = 2;
        $shipInfo->maxCrew = 2;
        $shipInfo->name = 'Cutlass Black';
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/ships/drake-cutlass/Cutlass-Black';
        $shipInfo->manufacturerName = 'Drake Interplanetary';
        $shipInfo->manufacturerCode = 'DRAK';
        $shipInfo->mediaUrl = 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/source/Drake_cutlass_storefront_visual.jpg';
        $shipInfo->mediaThumbUrl = 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/store_small/Drake_cutlass_storefront_visual.jpg';

        $shipInfos[] = $shipInfo;

        return $shipInfos;
    }
}
