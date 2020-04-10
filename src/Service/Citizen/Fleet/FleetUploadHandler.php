<?php

namespace App\Service\Citizen\Fleet;

use Algatux\InfluxDbBundle\Events\DeferredUdpEvent;
use App\Domain\Money;
use App\Domain\ShipInfo;
use App\Entity\Citizen;
use App\Entity\Fleet;
use App\Entity\Ship;
use App\Entity\ShipName;
use App\Event\CitizenFleetUpdatedEvent;
use App\Exception\BadCitizenException;
use App\Exception\FleetUploadedTooCloseException;
use App\Exception\InvalidFleetDataException;
use App\Repository\ShipNameRepository;
use App\Service\Citizen\CitizenRefresher;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use App\Service\Ship\InfosProvider\ShipInfosProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use InfluxDB\Database;
use InfluxDB\Point;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class FleetUploadHandler implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private EntityManagerInterface $entityManager;
    private CitizenInfosProviderInterface $citizenInfosProvider;
    private CitizenRefresher $citizenRefresher;
    private EventDispatcherInterface $eventDispatcher;
    private ShipNameRepository $shipNameRepository;
    private ShipInfosProviderInterface $shipInfosProvider;
    private RequestStack $requestStack;

    public function __construct(
        EntityManagerInterface $entityManager,
        CitizenInfosProviderInterface $citizenInfosProvider,
        CitizenRefresher $citizenRefresher,
        EventDispatcherInterface $eventDispatcher,
        ShipNameRepository $shipNameRepository,
        ShipInfosProviderInterface $shipInfosProvider,
        RequestStack $requestStack
    ) {
        $this->entityManager = $entityManager;
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->citizenRefresher = $citizenRefresher;
        $this->eventDispatcher = $eventDispatcher;
        $this->shipNameRepository = $shipNameRepository;
        $this->shipInfosProvider = $shipInfosProvider;
        $this->requestStack = $requestStack;
    }

    public function handle(Citizen $citizen, array $fleetData): void
    {
        try {
            $infos = $this->citizenInfosProvider->retrieveInfos($citizen->getActualHandle());
        } catch (\Exception $e) {
            throw new BadCitizenException($e->getMessage());
        }
        if (!$infos->numberSC->equals($citizen->getNumber())) {
            throw new BadCitizenException(sprintf('The SC number %s is not equal to %s.', $citizen->getNumber(), $infos->numberSC));
        }

        $this->citizenRefresher->refreshCitizen($citizen, $infos);
        $this->entityManager->flush();

        $lastVersion = $citizen->getLastFleet();
        if ($lastVersion !== null && $lastVersion->isUploadedDateTooClose()) {
            throw new FleetUploadedTooCloseException(sprintf('Last version of the fleet was uploaded at %s', $lastVersion->getUploadDate()->format('Y-m-d H:i')));
        }

        $fleet = $this->createNewFleet($fleetData, $lastVersion);

        // no diff : refresh the last version
        if ($lastVersion !== null && !$this->hasDiff($fleet, $lastVersion)) {
            $lastVersion->setRefreshDate(new \DateTimeImmutable());
            $this->entityManager->flush();

            return;
        }

        $fleet->setOwner($citizen);
        $fleet->setRefreshDate(new \DateTimeImmutable());
        $this->entityManager->persist($fleet);
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new CitizenFleetUpdatedEvent($citizen, $fleet, $lastVersion));
        $this->eventDispatcher->dispatch(new DeferredUdpEvent([new Point(
            'app.fleet_upload',
            1,
            ['citizen_id' => $citizen->getId(), 'citizen_handle' => (string) $citizen->getActualHandle(), 'host' => $this->requestStack->getCurrentRequest()->getHost()],
        )], Database::PRECISION_SECONDS), DeferredUdpEvent::NAME);
    }

    private function hasDiff(Fleet $newFleet, Fleet $lastFleet): bool
    {
        if (count($newFleet->getShips()) !== count($lastFleet->getShips())) {
            return true;
        }
        $countSameShips = 0;
        foreach ($newFleet->getShips() as $newShip) {
            foreach ($lastFleet->getShips() as $lastShip) {
                if ($newShip->equals($lastShip)) {
                    ++$countSameShips;
                    break;
                }
            }
        }

        return $countSameShips !== count($newFleet->getShips());
    }

    private function createNewFleet(array $fleetData, ?Fleet $lastVersionFleet = null): Fleet
    {
        if (!$this->isFleetDataValid($fleetData)) {
            throw new InvalidFleetDataException('The fleet data is invalid.');
        }

        $fleet = new Fleet(Uuid::uuid4());
        $fleet->setVersion($lastVersionFleet === null ? 1 : ($lastVersionFleet->getVersion() + 1));

        /*
         * 2 cases for each ship data:
         *   - ship name handled by a ship mapping name pattern => we retrieve their infos (name...)
         *   - ship name has no mapping => request ship info provider for a ship with a similar name
         */

        /** @var ShipName[] $shipMappings */
        $shipMappings = $this->shipNameRepository->findAllMappingsWithPatternAndProviderId();
        $notFoundMappingNames = [];
        $providerIds = [];
        foreach ($fleetData as $shipData) {
            $shipName = mb_strtolower(trim($shipData['name']));
            $mappingFound = false;
            foreach ($shipMappings as $shipMapping) {
                if (!preg_match('~'.str_replace('~', '\~', $shipMapping->getMyHangarNamePattern()).'~i', $shipName)) {
                    continue;
                }
                // found a mapping
                $providerIds[$shipMapping->getProviderId()->toString()] = $shipData; // for request all ship infos all-in-once
                $mappingFound = true;
                break;
            }
            if (!$mappingFound) {
                $notFoundMappingNames[$shipName] = $shipData; // for trying to get a ship info by the name
            }
        }

        $shipInfos = $this->shipInfosProvider->getShipsByIdOrName(array_keys($providerIds), array_keys($notFoundMappingNames));

        foreach ($providerIds as $providerId => $shipData) {
            $foundShipInfo = null;
            foreach ($shipInfos as $shipInfo) {
                if ($shipInfo->id === $providerId) {
                    $foundShipInfo = $shipInfo;
                    break;
                }
            }
            if ($foundShipInfo === null) {
                $this->logger->error('[FleetUploadHandler] The ship with provider Id {providerId} was not found. Check the ship mapping in BO.', ['providerId' => $providerId]);
            }
            $ship = $this->createShip($shipData, $foundShipInfo);
            $fleet->addShip($ship);
        }
        $collator = \Collator::create(null);
        $collator->setStrength(\Collator::PRIMARY); // first level compares for example : a === Â, but a < b
        foreach ($notFoundMappingNames as $shipName => $shipData) {
            $foundShipInfo = null;
            foreach ($shipInfos as $shipInfo) {
                if ($collator->compare($shipInfo->name, $shipName) === 0) {
                    $foundShipInfo = $shipInfo;
                    break;
                }
            }
            if ($foundShipInfo === null) {
                $this->logger->error('[FleetUploadHandler] The ship with name "{shipName}" was not found. You should add it to ship mapping in BO.', ['shipName' => $shipName]);
            }
            $ship = $this->createShip($shipData, $foundShipInfo);
            $fleet->addShip($ship);
        }

        return $fleet;
    }

    private function createShip(array $shipData, ?ShipInfo $shipInfo): Ship
    {
        $insuranceDuration = null;
        if (isset($shipData['insurance_duration'])) {
            $insuranceDuration = (int) $shipData['insurance_duration'];
        } elseif (isset($shipData['monthsInsurance'])) {
            $insuranceDuration = (int) $shipData['monthsInsurance'];
        }
        $ship = new Ship(Uuid::uuid4());
        $ship
            ->setName(trim($shipData['name']))
            ->setNormalizedName($shipInfo !== null ? $shipInfo->name : null)
            ->setGalaxyId($shipInfo !== null && $shipInfo->id !== null ? Uuid::fromString($shipInfo->id) : null)
            ->setManufacturer(trim($shipData['manufacturer']))
            ->setInsured((bool) ($shipData['lti'] ?? false))
            ->setInsuranceType($shipData['insurance_type'] ?? null)
            ->setInsuranceDuration($insuranceDuration)
            ->setPledgeDate(\DateTimeImmutable::createFromFormat('F d, Y', $shipData['pledge_date'])->setTime(0, 0))
            ->setRawData($shipData);
        if (isset($shipData['cost'])) {
            $ship->setCost((new Money((float) str_replace(',', '', preg_replace('~^\$(\d+(,\d+)*\.\d+)~', '$1', $shipData['cost']))))->getCost());
        }

        return $ship;
    }

    private function isFleetDataValid(array $fleetData): bool
    {
        foreach ($fleetData as $shipData) {
            if (!isset(
                $shipData['pledge_date'],
                $shipData['manufacturer'],
                $shipData['name']
            )) {
                $this->logger->error('[FleetDataInvalid] either pledge_date, manufacturer or name key is empty.', ['ship_data' => $shipData]);

                return false;
            }

            $date = \DateTimeImmutable::createFromFormat('F d, Y', $shipData['pledge_date']);
            if ($date === false) {
                $this->logger->error('[FleetDataInvalid] the pledge_date is not in a good format.', ['ship_data' => $shipData]);

                return false;
            }
            if (isset($shipData['cost']) && !preg_match('~^\$(\d+(,\d+)*\.\d+)~', $shipData['cost'])) {
                $this->logger->error('[FleetDataInvalid] the cost is not in a good format.', ['ship_data' => $shipData]);

                return false;
            }
            if (isset($shipData['insurance_type']) && !in_array($shipData['insurance_type'], Ship::INSURANCE_TYPES, true)) {
                $this->logger->error('[FleetDataInvalid] the insurance_type is not valid.', ['ship_data' => $shipData]);

                return false;
            }
        }

        return true;
    }
}
