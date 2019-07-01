<?php

namespace App\Service;

use App\Domain\Money;
use App\Entity\Citizen;
use App\Entity\Fleet;
use App\Entity\Ship;
use App\Event\CitizenFleetUpdatedEvent;
use App\Exception\BadCitizenException;
use App\Exception\FleetUploadedTooCloseException;
use App\Exception\InvalidFleetDataException;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class FleetUploadHandler
{
    private $entityManager;
    private $citizenInfosProvider;
    private $citizenRefresher;
    private $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        CitizenInfosProviderInterface $citizenInfosProvider,
        CitizenRefresher $citizenRefresher,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->citizenInfosProvider = $citizenInfosProvider;
        $this->citizenRefresher = $citizenRefresher;
        $this->eventDispatcher = $eventDispatcher;
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
            throw new FleetUploadedTooCloseException(
                sprintf('Last version of the fleet was uploaded at %s', $lastVersion->getUploadDate()->format('Y-m-d H:i')));
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

        foreach ($fleetData as $shipData) {
            $ship = new Ship(Uuid::uuid4());
            $ship
                ->setName(trim($shipData['name']))
                ->setManufacturer(trim($shipData['manufacturer']))
                ->setInsured((bool) $shipData['lti'])
                ->setCost((new Money((int) preg_replace('/^\$(\d+\.\d+)/', '$1', $shipData['cost'])))->getCost())
                ->setPledgeDate(\DateTimeImmutable::createFromFormat('F d, Y', $shipData['pledge_date'])->setTime(0, 0))
                ->setRawData($shipData);
            $fleet->addShip($ship);
        }

        return $fleet;
    }

    private function isFleetDataValid(array $fleetData): bool
    {
        foreach ($fleetData as $shipData) {
            if (!isset(
                $shipData['pledge_date'],
                $shipData['manufacturer'],
                $shipData['name'],
                $shipData['lti'],
                $shipData['cost']
            )) {
                return false;
            }
            $date = \DateTimeImmutable::createFromFormat('F d, Y', $shipData['pledge_date']);
            if ($date === false) {
                return false;
            }
            if (preg_replace('/^\$(\d+\.\d+)/i', '$1', $shipData['cost']) === null) {
                return false;
            }
        }

        return true;
    }
}
