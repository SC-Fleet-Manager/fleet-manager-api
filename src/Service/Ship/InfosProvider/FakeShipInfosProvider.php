<?php

namespace App\Service\Ship\InfosProvider;

use App\Domain\ShipInfo;

class FakeShipInfosProvider extends ApiShipInfosProvider
{
    /**
     * @return iterable|ShipInfo[]
     */
    public function refreshShips(): array
    {
        return $this->getAllShips();
    }

    public function getAllShips(): array
    {
        $shipInfos = [];

        // Cutlass Black
        $shipInfo = new ShipInfo();
        $shipInfo->id = 'e37c618b-3ec6-4d4d-92b6-5aed679962a2';
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
        $shipInfo->chassisId = 'f92c7c98-a8d2-4c79-b34c-c728d4fffbfc';
        $shipInfo->chassisName = 'Cutlass';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // Aurora MR
        $shipInfo = new ShipInfo();
        $shipInfo->id = 'cbcb60c7-a780-4a59-b51d-0ad8021813bf';
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
        $shipInfo->chassisId = '8502c9fd-6b1a-47e1-a7fc-6cb034b94da1';
        $shipInfo->chassisName = 'Aurora';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // Ranger CV
        $shipInfo = new ShipInfo();
        $shipInfo->id = 'f250a2b7-76ea-481f-84b5-3e2e96d40e84';
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
        $shipInfo->chassisId = '4f99495f-0d0b-49b3-91e7-9258faded5a8';
        $shipInfo->chassisName = 'Ranger';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // Dragonfly Black
        $shipInfo = new ShipInfo();
        $shipInfo->id = '05e980c5-6425-4fe4-a3c2-d69a0d568e40';
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
        $shipInfo->chassisId = 'b395ea78-3d0b-400c-b527-862a95cf3f1c';
        $shipInfo->chassisName = 'Dragonfly';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // Constellation Andromeda
        $shipInfo = new ShipInfo();
        $shipInfo->id = 'f43fa89e-d34f-43d2-807d-5e8bf8c8929a';
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
        $shipInfo->chassisId = '0abc242a-b700-4c84-977d-93646ddab2fa';
        $shipInfo->chassisName = 'Constellation';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // Orion
        $shipInfo = new ShipInfo();
        $shipInfo->id = '9950adb5-9151-4760-9073-080416120fca';
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
        $shipInfo->chassisId = 'eff1490f-e30f-4ef0-9ffd-31b993d933be';
        $shipInfo->chassisName = 'Orion';
        $shipInfos[$shipInfo->id] = $shipInfo;

        return $shipInfos;
    }
}
