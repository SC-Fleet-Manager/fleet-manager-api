<?php

namespace App\Service;

use App\Domain\Money;
use App\Entity\Citizen;
use App\Entity\Fleet;
use App\Entity\Ship;
use App\Exception\BadCitizenException;
use App\Exception\FleetUploadedTooCloseException;
use App\Exception\InvalidFleetDataException;
use App\Repository\CitizenRepository;
use App\Repository\FleetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class FleetUploadHandler
{
    private $fleetRepository;
    private $citizenRepository;
    private $entityManager;
    private $citizenInfosProvider;

    public function __construct(
        FleetRepository $fleetRepository,
        CitizenRepository $citizenRepository,
        EntityManagerInterface $entityManager,
        CitizenInfosProviderInterface $citizenInfosProvider
    ) {
        $this->fleetRepository = $fleetRepository;
        $this->citizenRepository = $citizenRepository;
        $this->entityManager = $entityManager;
        $this->citizenInfosProvider = $citizenInfosProvider;
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

        $citizen->setBio($infos->bio);
        foreach ($infos->organisations as $organisation) {
            $citizen->addOrganisation(clone $organisation);
        }
        $this->entityManager->flush();

        $lastVersion = $this->fleetRepository->getLastVersionFleet($citizen);
        if ($lastVersion !== null && $lastVersion->isUploadedDateTooClose()) {
            throw new FleetUploadedTooCloseException(
                sprintf('Last version of the fleet was uploaded on %s', $lastVersion->getUploadDate()->format('Y-m-d H:i')));
        }

        $fleet = $this->createNewFleet($citizen, $fleetData, $lastVersion);
        $this->entityManager->persist($fleet);
        $this->entityManager->flush();
    }

    private function createNewFleet(Citizen $citizen, array $fleetData, ?Fleet $lastVersionFleet = null): Fleet
    {
        if (!$this->isFleetDataValid($fleetData)) {
            throw new InvalidFleetDataException('The fleet data is invalid.');
        }

        $fleet = new Fleet(Uuid::uuid4());
        $fleet->setOwner($citizen);
        $fleet->setVersion($lastVersionFleet->getVersion() + 1);
        foreach ($fleetData as $shipData) {
            $ship = new Ship(Uuid::uuid4());
            $ship
                ->setName($shipData['name'])
                ->setManufacturer($shipData['manufacturer'])
                ->setInsured($shipData['lti'])
                ->setCost((new Money((int) preg_replace('/^\$(\d+\.\d+)/i', '$1', $shipData['cost'])))->getCost())
                ->setPledgeDate(\DateTimeImmutable::createFromFormat('F d, Y', $shipData['pledge_date']))
                ->setRawData($shipData)
            ;
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