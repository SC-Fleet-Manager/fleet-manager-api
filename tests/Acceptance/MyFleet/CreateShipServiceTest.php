<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\Common\Clock;
use App\Application\MyFleet\CreateShipService;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Common\FakeClock;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;

class CreateShipServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_create_a_ship_to_the_existing_fleet_of_logged_user(): void
    {
        $userId = UserId::fromString('00000000000000000000000001');

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([
            new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00')),
        ]);

        /** @var CreateShipService $service */
        $service = static::$container->get(CreateShipService::class);
        $service->handle($userId, ShipId::fromString('00000000000000000000000020'), 'Avenger', 'https://example.com/picture.jpg');

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(1, $fleet->getShips());
        static::assertSame('00000000000000000000000020', (string) $fleet->getShips()[0]->getId());
        static::assertSame('Avenger', $fleet->getShips()[0]->getName());
        static::assertSame('https://example.com/picture.jpg', $fleet->getShips()[0]->getImageUrl());
        static::assertSame(1, $fleet->getShips()[0]->getQuantity());
    }

    /**
     * @test
     */
    public function it_should_create_a_ship_to_a_new_fleet_of_logged_user(): void
    {
        $userId = UserId::fromString('00000000000000000000000001');

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([]);

        /** @var FakeClock $clock */
        $clock = static::$container->get(Clock::class);
        $clock->setNow('2021-01-01T10:00:00Z');

        /** @var CreateShipService $service */
        $service = static::$container->get(CreateShipService::class);
        $service->handle($userId, ShipId::fromString('00000000000000000000000020'), 'Avenger', 'https://example.com/picture.jpg');

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertNotNull($fleet);
        static::assertEquals(new \DateTimeImmutable('2021-01-01T10:00:00Z'), $fleet->getUpdatedAt());
        static::assertCount(1, $fleet->getShips());
    }
}
