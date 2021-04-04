<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\Exception\NotFoundFleetByUserException;
use App\Application\MyFleet\MyFleetService;
use App\Application\MyFleet\Output\MyFleetOutput;
use App\Application\MyFleet\Output\MyFleetShipOutput;
use App\Application\MyFleet\Output\MyFleetShipsCollectionOutput;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\FleetId;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Entity\Ship;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;

class MyFleetServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_the_fleet_and_ships_of_logged_user(): void
    {
        $fleet = new Fleet(FleetId::fromString('00000000000000000000000010'), UserId::fromString('00000000000000000000000001'), new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000000000000000000020'), 'Avenger', null, 2, new \DateTimeImmutable('2021-01-01T13:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000000000000000000021'), 'Mercury Star Runner', 'https://example.com/mercury.jpg', 10, new \DateTimeImmutable('2021-01-01T14:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000000000000000000022'), 'Javelin', 'https://example.com/javelin.jpg', 1, new \DateTimeImmutable('2021-01-01T15:00:00+02:00'));

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var MyFleetService $service */
        $service = static::$container->get(MyFleetService::class);
        $output = $service->handle(UserId::fromString('00000000000000000000000001'));

        static::assertEquals(new MyFleetOutput(
            id: FleetId::fromString('00000000000000000000000010'),
            ships: new MyFleetShipsCollectionOutput(
                items: [
                    new MyFleetShipOutput(
                        id: ShipId::fromString('00000000000000000000000020'),
                        name: 'Avenger',
                        imageUrl: null,
                        quantity: 2,
                    ),
                    new MyFleetShipOutput(
                        id: ShipId::fromString('00000000000000000000000021'),
                        name: 'Mercury Star Runner',
                        imageUrl: 'https://example.com/mercury.jpg',
                        quantity: 10,
                    ),
                    new MyFleetShipOutput(
                        id: ShipId::fromString('00000000000000000000000022'),
                        name: 'Javelin',
                        imageUrl: 'https://example.com/javelin.jpg',
                        quantity: 1,
                    ),
                ],
                count: 3,
            ),
            updatedAt: new \DateTimeImmutable('2021-01-01T13:00:00Z'),
        ), $output);
    }

    /**
     * @test
     */
    public function it_should_return_a_fleet_with_no_ships(): void
    {
        $fleet = new Fleet(FleetId::fromString('00000000000000000000000010'), UserId::fromString('00000000000000000000000001'), new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var MyFleetService $service */
        $service = static::$container->get(MyFleetService::class);
        $output = $service->handle(UserId::fromString('00000000000000000000000001'));

        static::assertEquals(new MyFleetOutput(
            id: FleetId::fromString('00000000000000000000000010'),
            ships: new MyFleetShipsCollectionOutput(
            items: [],
            count: 0,
        ),
            updatedAt: new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ), $output);
    }

    /**
     * @test
     */
    public function it_should_throw_error_if_not_found_fleet(): void
    {
        $this->expectException(NotFoundFleetByUserException::class);

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([
            new Fleet(FleetId::fromString('00000000000000000000000010'), UserId::fromString('00000000000000000000000002'), new \DateTimeImmutable('2021-01-01T12:00:00+02:00')), // other user
        ]);

        /** @var MyFleetService $service */
        $service = static::$container->get(MyFleetService::class);
        $service->handle(UserId::fromString('00000000000000000000000001'));
    }
}
