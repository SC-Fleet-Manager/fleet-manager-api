<?php

namespace App\Service;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Service\Dto\ShipFamilyFilter;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class OrganizationFleetHandler
{
    private $shipInfosProvider;
    private $entityManager;
    private $logger;

    public function __construct(ShipInfosProviderInterface $shipInfosProvider, EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->shipInfosProvider = $shipInfosProvider;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    /**
     * @return array e.g. [['chassisId' => '1', 'name' => 'Aurora', 'count' => 4, 'manufacturerCode' => 'RSI'], [...]]
     */
    public function computeShipFamilies(SpectrumIdentification $organizationId, ShipFamilyFilter $filter): array
    {
        $shipInfos = $this->shipInfosProvider->getAllShips();
        $orgaShips = $this->entityManager->getRepository(Citizen::class)->getOrganizationShips($organizationId, $filter);

        $shipFamilies = [];
        foreach ($orgaShips as $orgaShip) {
            // search chassisId of the orgaShip
            $found = false;
            foreach ($shipInfos as $shipInfo) {
                if (!$this->shipInfosProvider->shipNamesAreEquals($orgaShip->getName(), $shipInfo->name)) {
                    continue;
                }
                $found = true;
                // filtering
                if (count($filter->shipSizes) > 0 && !in_array($shipInfo->size, $filter->shipSizes, false)) {
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
                break;
            }
            if (!$found) {
                $this->logger->warning('A persited ship was not found in the shipInfosPovider', ['orgaShip' => $orgaShip->getId(), 'shipName' => $orgaShip->getName(), 'shipInfosProvider' => get_class($this->shipInfosProvider)]);
            }
        }

        return $shipFamilies;
    }
}
