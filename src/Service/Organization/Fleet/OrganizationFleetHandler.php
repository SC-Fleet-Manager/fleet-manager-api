<?php

namespace App\Service\Organization\Fleet;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Service\Dto\ShipFamilyFilter;
use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OrganizationFleetHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private ShipInfosProviderInterface $shipInfosProvider;
    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipInfosProviderInterface $shipInfosProvider,
        EntityManagerInterface $entityManager
    ) {
        $this->shipInfosProvider = $shipInfosProvider;
        $this->entityManager = $entityManager;
    }

    /**
     * @return array e.g. [['chassisId' => 'f94c62fd-...', 'name' => 'Aurora', 'count' => 4, 'manufacturerCode' => 'RSI'], [...]]
     */
    public function computeShipFamilies(SpectrumIdentification $organizationId, ShipFamilyFilter $filter): array
    {
        $countShipSizesFilters = count($filter->shipSizes);
        $orgaShips = $this->entityManager->getRepository(Citizen::class)->getOrganizationShips($organizationId, $filter);

        $shipIds = [];
        foreach ($orgaShips as $orgaShip) {
            if ($orgaShip->getGalaxyId() !== null) {
                $shipIds[] = $orgaShip->getGalaxyId()->toString();
            }
        }

        $shipInfos = $this->shipInfosProvider->getShipsByIdOrName($shipIds);

        $shipFamilies = [];
        foreach ($orgaShips as $orgaShip) {
            $shipInfo = $orgaShip->getGalaxyId() !== null && isset($shipInfos[$orgaShip->getGalaxyId()->toString()])
                ? $shipInfos[$orgaShip->getGalaxyId()->toString()]
                : null;
            if ($shipInfo === null) {
                // *** A ship is missing. ***
                continue;
            }
            if ($shipInfo->chassisId === null) {
                $this->logger->warning('[OrgaFleetHandler] The ship info "{shipName}" has no ChassisId.', ['shipName' => $shipInfo->name, 'shipId' => $shipInfo->id]);
                continue;
            }
            if ($countShipSizesFilters > 0 && !in_array($shipInfo->size, $filter->shipSizes, false)) {
                continue;
            }
            if ($filter->shipStatus !== null && $filter->shipStatus !== $shipInfo->productionStatus) {
                continue;
            }
            if (!isset($shipFamilies[$shipInfo->chassisId])) {
                $shipFamilies[$shipInfo->chassisId] = [
                    'chassisId' => $shipInfo->chassisId,
                    'name' => $shipInfo->chassisName,
                    'count' => 1,
                    'manufacturerCode' => $shipInfo->manufacturerCode,
                    'mediaThumbUrl' => $shipInfo->mediaThumbUrl,
                ];
            } else {
                ++$shipFamilies[$shipInfo->chassisId]['count'];
            }
        }

        return $shipFamilies;
    }
}
