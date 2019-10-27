<?php

namespace App\Service\Ship\InfosProvider;

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

        // Ranger CV
        $shipInfo = new ShipInfo();
        $shipInfo->id = '182';
        $shipInfo->productionStatus = ShipInfo::NOT_READY;
        $shipInfo->minCrew = 1;
        $shipInfo->maxCrew = 1;
        $shipInfo->name = 'Ranger CV';
        $shipInfo->size = ShipInfo::SIZE_VEHICLE;
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/ships/tumbril-ranger/Ranger-CV';
        $shipInfo->manufacturerName = 'Tumbril';
        $shipInfo->manufacturerCode = 'TMBL';
        $shipInfo->mediaUrl = 'https://robertsspaceindustries.com/media/a9ukhl4werezmr/source/Cargo-Min.jpg';
        $shipInfo->mediaThumbUrl = 'https://robertsspaceindustries.com/media/a9ukhl4werezmr/store_small/Cargo-Min.jpg';
        $shipInfo->chassisId = '74';
        $shipInfo->chassisName = 'Ranger';
        $shipInfos[] = $shipInfo;

        // Dragonfly Black
        $shipInfo = new ShipInfo();
        $shipInfo->id = '112';
        $shipInfo->productionStatus = ShipInfo::FLIGHT_READY;
        $shipInfo->minCrew = 1;
        $shipInfo->maxCrew = 2;
        $shipInfo->name = 'Dragonfly Black';
        $shipInfo->size = ShipInfo::SIZE_SNUB;
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/ships/drake-dragonfly/Dragonfly-Black';
        $shipInfo->manufacturerName = 'Drake Interplanetary';
        $shipInfo->manufacturerCode = 'DRAK';
        $shipInfo->mediaUrl = 'https://robertsspaceindustries.com/media/5v25a4lwtbdlar/source/Dragonfly-Black-Left.jpg';
        $shipInfo->mediaThumbUrl = 'https://robertsspaceindustries.com/media/5v25a4lwtbdlar/store_small/Dragonfly-Black-Left.jpg';
        $shipInfo->chassisId = '42';
        $shipInfo->chassisName = 'Dragonfly';
        $shipInfos[] = $shipInfo;

        // Constellation Andromeda
        $shipInfo = new ShipInfo();
        $shipInfo->id = '45';
        $shipInfo->productionStatus = ShipInfo::FLIGHT_READY;
        $shipInfo->minCrew = 3;
        $shipInfo->maxCrew = 4;
        $shipInfo->name = 'Constellation Andromeda';
        $shipInfo->size = ShipInfo::SIZE_LARGE;
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/ships/rsi-constellation/Constellation-Andromeda';
        $shipInfo->manufacturerName = 'Roberts Space Industries';
        $shipInfo->manufacturerCode = 'RSI';
        $shipInfo->mediaUrl = 'https://robertsspaceindustries.com/media/vzyhde6cjgsn7r/source/Andromeda_Storefront.jpg';
        $shipInfo->mediaThumbUrl = 'https://robertsspaceindustries.com/media/vzyhde6cjgsn7r/store_small/Andromeda_Storefront.jpg';
        $shipInfo->chassisId = '4';
        $shipInfo->chassisName = 'Constellation';
        $shipInfos[] = $shipInfo;

        // Orion
        $shipInfo = new ShipInfo();
        $shipInfo->id = '71';
        $shipInfo->productionStatus = ShipInfo::NOT_READY;
        $shipInfo->minCrew = 4;
        $shipInfo->maxCrew = 7;
        $shipInfo->name = 'Orion';
        $shipInfo->size = ShipInfo::SIZE_CAPITAL;
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/ships/orion/Orion';
        $shipInfo->manufacturerName = 'Roberts Space Industries';
        $shipInfo->manufacturerCode = 'RSI';
        $shipInfo->mediaUrl = 'https://robertsspaceindustries.com/media/hfpnkupg7gr6er/source/RSI_Orion_Situ1b_150219_GH.jpg';
        $shipInfo->mediaThumbUrl = 'https://robertsspaceindustries.com/media/hfpnkupg7gr6er/store_small/RSI_Orion_Situ1b_150219_GH.jpg';
        $shipInfo->chassisId = '25';
        $shipInfo->chassisName = 'Orion';
        $shipInfos[] = $shipInfo;

        return $shipInfos;
    }
}
