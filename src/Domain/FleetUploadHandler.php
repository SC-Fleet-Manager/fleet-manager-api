<?php

namespace App\Domain;

use App\Domain\Exception\FleetUploadedTooCloseException;
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
    public function handle(HandleSC $handleSC, array $fleetData): void
    {
        $infos = $this->citizenInfosProvider->retrieveInfos($handleSC);

        // Citizen already persisted ?
        // TODO : getByNumber instead ?
        $citizen = $this->citizenRepository->getByHandle($handleSC);
        if ($citizen === null) {
            // create new citizen
            $citizen = new Citizen(Uuid::uuid4());
            $citizen->number = clone $infos->numberSC;
            $citizen->actualHandle = clone $infos->handle;
            $citizen->organisations = [];
            foreach ($infos->organisations as $organisation) {
                $citizen->organisations[] = clone $organisation;
            }
            $this->citizenRepository->create($citizen);
        } else {
            // update citizen
            $citizen->number = clone $infos->numberSC;
            $citizen->actualHandle = clone $infos->handle;
            $citizen->organisations = [];
            foreach ($infos->organisations as $organisation) {
                $citizen->organisations[] = clone $organisation;
            }
            $this->citizenRepository->update($citizen);
        }

        $lastVersion = $this->fleetRepository->getLastVersionFleet($citizen);

        if ($lastVersion !== null && $lastVersion->isUploadedDateTooClose()) {
            throw new FleetUploadedTooCloseException(
                sprintf('Last version of the fleet was uploaded on %s', $lastVersion->uploadDate->format('Y-m-d H:i')));
        }

        $fleet = $this->createNewFleet($citizen, $fleetData);

        $this->fleetRepository->save($fleet);
    }

    private function createNewFleet(Citizen $citizen, array $fleetData): Fleet
    {
        $fleet = new Fleet(Uuid::uuid4(), $citizen);
        $fleet->version = ($lastVersion->version ?? 0) + 1;
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
}
