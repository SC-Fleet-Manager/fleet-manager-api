<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Domain\Exception\NotFoundFleetByUserException;
use App\Application\MyFleet\MyFleetService;
use App\Application\MyFleet\Output\MyFleetOutput;
use App\Application\MyFleet\Output\MyFleetShipOutput;
use App\Application\MyFleet\Output\MyFleetShipsCollectionOutput;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;

class MyFleetServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_the_fleet_and_ships_of_logged_user(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000010'), 'Avenger', null, 2, new \DateTimeImmutable('2021-01-01T13:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000011'), 'Mercury Star Runner', 'https://example.com/mercury.jpg', 10, new \DateTimeImmutable('2021-01-01T14:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000012'), 'Javelin', 'https://example.com/javelin.jpg', 1, new \DateTimeImmutable('2021-01-01T15:00:00+02:00'));

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var MyFleetService $service */
        $service = static::$container->get(MyFleetService::class);
        $output = $service->handle($userId);

        static::assertEquals(new MyFleetOutput(
            new MyFleetShipsCollectionOutput(
                [
                    new MyFleetShipOutput(
                        id: ShipId::fromString('00000000-0000-0000-0000-000000000010'),
                        model: 'Avenger',
                        imageUrl: null,
                        quantity: 2,
                    ),
                    new MyFleetShipOutput(
                        id: ShipId::fromString('00000000-0000-0000-0000-000000000011'),
                        model: 'Mercury Star Runner',
                        imageUrl: 'https://example.com/mercury.jpg',
                        quantity: 10,
                    ),
                    new MyFleetShipOutput(
                        id: ShipId::fromString('00000000-0000-0000-0000-000000000012'),
                        model: 'Javelin',
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
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var MyFleetService $service */
        $service = static::$container->get(MyFleetService::class);
        $output = $service->handle($userId);

        static::assertEquals(
            new MyFleetOutput(
                new MyFleetShipsCollectionOutput(
                    items: [],
                    count: 0,
                ),
                updatedAt: new \DateTimeImmutable('2021-01-01T10:00:00Z'),
            ), $output,
        );
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
            new Fleet(UserId::fromString('00000000-0000-0000-0000-000000000002'), new \DateTimeImmutable('2021-01-01T12:00:00+02:00')), // other user
        ]);

        /** @var MyFleetService $service */
        $service = static::$container->get(MyFleetService::class);
        $service->handle(UserId::fromString('00000000-0000-0000-0000-000000000001'));
    }
}
