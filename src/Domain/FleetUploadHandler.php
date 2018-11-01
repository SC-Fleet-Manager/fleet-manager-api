<?php

namespace App\Domain;

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
     * {@inheritDoc}
     */
    function handle(HandleSC $handleSC, array $fleetData): void
    {
        $infos = $this->citizenInfosProvider->retrieveInfos($handleSC);

        // TODO : add check for organisation ?

        // Citizen already persisted ?
        $citizen = $this->citizenRepository->getByHandle($handleSC);
        if ($citizen === null) {
            // create new citizen
            $citizen = new Citizen(Uuid::uuid4());
            $citizen->number = clone $infos->numberSC;
            $citizen->actualHandle = clone $infos->handle;
            foreach ($infos->organisations as $organisation) {
                $citizen->organisations[] = clone $organisation;
            }
            $this->citizenRepository->create($citizen);
        }

        // get last version fleet
        $lastVersion = $this->fleetRepository->getLastVersionFleet($citizen);

        // add new fleet for this citizen
        $fleet = new Fleet(Uuid::uuid4(), $citizen);
        $fleet->version = $lastVersion + 1;
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

        $this->fleetRepository->save($fleet);
    }
}
