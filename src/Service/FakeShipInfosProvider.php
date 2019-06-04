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
        $shipInfo->size = ShipInfo::SIZE_MEDIUM;
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/ships/drake-cutlass/Cutlass-Black';
        $shipInfo->manufacturerName = 'Drake Interplanetary';
        $shipInfo->manufacturerCode = 'DRAK';
        $shipInfo->mediaUrl = 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/source/Drake_cutlass_storefront_visual.jpg';
        $shipInfo->mediaThumbUrl = 'https://robertsspaceindustries.com/media/7tcxllnna6a9hr/store_small/Drake_cutlass_storefront_visual.jpg';
        $shipInfo->chassisId = '6';
        $shipInfo->chassisName = 'Cutlass';
        $shipInfos[] = $shipInfo;

        // Aurora MR
        $shipInfo = new ShipInfo();
        $shipInfo->id = '4';
        $shipInfo->productionStatus = ShipInfo::FLIGHT_READY;
        $shipInfo->minCrew = 1;
        $shipInfo->maxCrew = 1;
        $shipInfo->name = 'Aurora MR';
        $shipInfo->size = ShipInfo::SIZE_SMALL;
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/ships/rsi-aurora/Aurora-MR';
        $shipInfo->manufacturerName = 'Roberts Space Industries';
        $shipInfo->manufacturerCode = 'RSI';
        $shipInfo->mediaUrl = 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/source/Rsi_aurora_mr_storefront_visual.jpg';
        $shipInfo->mediaThumbUrl = 'https://robertsspaceindustries.com/media/ohbfgn1ebcsnar/store_small/Rsi_aurora_mr_storefront_visual.jpg';
        $shipInfo->chassisId = '1';
        $shipInfo->chassisName = 'Aurora';
        $shipInfos[] = $shipInfo;

        return $shipInfos;
    }
}
