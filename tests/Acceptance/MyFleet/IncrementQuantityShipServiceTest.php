<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\Common\Clock;
use App\Application\MyFleet\CreateShipService;
use App\Application\MyFleet\IncrementQuantityShipService;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Common\FakeClock;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;

class IncrementQuantityShipServiceTest extends KernelTestCase
{
    /**
     * @test
     * @dataProvider it_should_increment_by_X_the_quantity_of_a_ship_of_logged_user_provider
     */
    public function it_should_increment_by_X_the_quantity_of_a_ship_of_logged_user(int $startQuantity, int $step, int $endQuantity): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $shipId = ShipId::fromString('00000000-0000-0000-0000-000000000010');

        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip($shipId, 'Avenger', null, $startQuantity, new \DateTimeImmutable('2021-01-01T13:00:00+02:00'));

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var IncrementQuantityShipService $service */
        $service = static::$container->get(IncrementQuantityShipService::class);
        $service->handle($userId, $shipId, $step);

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertSame($endQuantity, $fleet->getShips()[(string) $shipId]->getQuantity());
    }

    public function it_should_increment_by_X_the_quantity_of_a_ship_of_logged_user_provider(): iterable
    {
        yield [2, 1, 3];
        yield [1, 3, 4];
        yield [2, -1, 1];
        yield [4, -2, 2];
    }

    /**
     * @test
     */
    public function it_should_return_1_if_the_potential_resulted_quantity_is_less_than_1(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $shipId = ShipId::fromString('00000000-0000-0000-0000-000000000010');

        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip($shipId, 'Avenger', null, 3, new \DateTimeImmutable('2021-01-01T13:00:00+02:00'));

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var IncrementQuantityShipService $service */
        $service = static::$container->get(IncrementQuantityShipService::class);
        $service->handle($userId, $shipId, -3);

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertSame(1, $fleet->getShips()[(string) $shipId]->getQuantity());
    }
}
