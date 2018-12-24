<?php

namespace App\Domain;

use App\Domain\Exception\BadCitizenException;
use App\Domain\Exception\FleetUploadedTooCloseException;
use App\Domain\Exception\InvalidFleetDataException;
use Ramsey\Uuid\Uuid;

class FleetUploadHandler implements FleetUploadHandlerInterface
{
    /**
     * @var FleetRepositoryInterface
     */
    private $fleetRepository;

    /**
     * @var CitizenRepositoryInterface
     */
    private $citizenRepository;

    /**
     * @var CitizenInfosProviderInterface
     */
    private $citizenInfosProvider;

    public function __construct(
        FleetRepositoryInterface $fleetRepository,
        CitizenRepositoryInterface $citizenRepository,
        CitizenInfosProviderInterface $citizenInfosProvider)
    {
        $this->fleetRepository = $fleetRepository;
        $this->citizenRepository = $citizenRepository;
        $this->citizenInfosProvider = $citizenInfosProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Citizen $citizen, array $fleetData): void
    {
        try {
            $infos = $this->citizenInfosProvider->retrieveInfos($citizen->actualHandle);
        } catch (\Exception $e) {
            throw new BadCitizenException($e->getMessage());
        }
        if (!$infos->numberSC->equals($citizen->number)) {
            throw new BadCitizenException(sprintf('The SC number %s is not equal to %s.', $citizen->number, $infos->numberSC));
        }

        $citizen->bio = $infos->bio;
        $citizen->organisations = [];
        foreach ($infos->organisations as $organisation) {
            $citizen->organisations[] = clone $organisation;
        }
        $this->citizenRepository->update($citizen);

        $lastVersion = $this->fleetRepository->getLastVersionFleet($citizen);

        if ($lastVersion !== null && $lastVersion->isUploadedDateTooClose()) {
            throw new FleetUploadedTooCloseException(
                sprintf('Last version of the fleet was uploaded on %s', $lastVersion->uploadDate->format('Y-m-d H:i')));
        }

        $fleet = $this->createNewFleet($citizen, $fleetData, $lastVersion);

        $this->fleetRepository->save($fleet);
    }

    private function createNewFleet(Citizen $citizen, array $fleetData, ?Fleet $lastVersionFleet = null): Fleet
    {
        if (!$this->isFleetDataValid($fleetData)) {
            throw new InvalidFleetDataException('The fleet data is invalid.');
        }

        $fleet = new Fleet(Uuid::uuid4(), $citizen);
        $fleet->version = ($lastVersionFleet->version ?? 0) + 1;
        $fleet->uploadDate = new \DateTimeImmutable();
        foreach ($fleetData as $shipData) {
            $ship = new Ship(Uuid::uuid4(), $citizen);
            $ship->manufacturer = $shipData['manufacturer'];
            $ship->name = $shipData['name'];
            $ship->insured = $shipData['lti'];
            $ship->cost = new Money((int) preg_replace('/^\$(\d+\.\d+)/i', '$1', $shipData['cost']));
            $ship->pledgeDate = \DateTimeImmutable::createFromFormat('F d, Y', $shipData['pledge_date']);
            $ship->rawData = $shipData;
            $fleet->ships[] = $ship;
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
