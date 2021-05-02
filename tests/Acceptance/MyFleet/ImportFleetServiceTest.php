<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\MyFleet\ImportFleetService;
use App\Application\MyFleet\Input\ImportFleetShip;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Event\UpdatedFleetShipEvent;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class ImportFleetServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_import_ships_from_hangar_transfer_format_file(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000010'), 'Avenger', 'https://example.com/avenger.jpg', 2, new \DateTimeImmutable('2021-01-01T10:00:00Z'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000011'), 'Javelin', null, 1, new \DateTimeImmutable('2021-01-02T10:00:00Z'));
        $fleet->getAndClearEvents();

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var ImportFleetService $service */
        $service = static::$container->get(ImportFleetService::class);
        $service->handle($userId, [
            new ImportFleetShip('Avenger'),
            new ImportFleetShip('Mercury Star Runner'),
            new ImportFleetShip('Mercury Star Runner'),
        ], onlyMissing: false);

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(3, $fleet->getShips());
        static::assertSame(3, $fleet->getShipByModel('Avenger')->getQuantity());
        static::assertSame('https://example.com/avenger.jpg', $fleet->getShipByModel('Avenger')->getImageUrl());
        static::assertSame(1, $fleet->getShipByModel('Javelin')->getQuantity());
        static::assertSame(2, $fleet->getShipByModel('Mercury Star Runner')->getQuantity());
        static::assertNull($fleet->getShipByModel('Mercury Star Runner')->getImageUrl());

        /** @var InMemoryTransport $transport */
        $transport = static::$container->get('messenger.transport.organizations_sub');
        static::assertCount(3, $transport->getSent());
        /** @var UpdatedFleetShipEvent $message */
        $message = $transport->getSent()[0]->getMessage();
        static::assertInstanceOf(UpdatedFleetShipEvent::class, $message);
        static::assertEquals(new UpdatedFleetShipEvent(
            $userId,
            'Avenger',
            'https://example.com/avenger.jpg',
            3,
        ), $message);
        /** @var UpdatedFleetShipEvent $message */
        $message = $transport->getSent()[1]->getMessage();
        static::assertInstanceOf(UpdatedFleetShipEvent::class, $message);
        static::assertEquals(new UpdatedFleetShipEvent(
            $userId,
            'Mercury Star Runner',
            null,
            1,
        ), $message);
        /** @var UpdatedFleetShipEvent $message */
        $message = $transport->getSent()[2]->getMessage();
        static::assertInstanceOf(UpdatedFleetShipEvent::class, $message);
        static::assertEquals(new UpdatedFleetShipEvent(
            $userId,
            'Mercury Star Runner',
            null,
            2,
        ), $message);
    }

    /**
     * @test
     */
    public function it_should_import_only_missing_ships(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000010'), 'Avenger', 'https://example.com/avenger.jpg', 2, new \DateTimeImmutable('2021-01-01T10:00:00Z'));
        $fleet->getAndClearEvents();

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var ImportFleetService $service */
        $service = static::$container->get(ImportFleetService::class);
        $service->handle($userId, [
            new ImportFleetShip('Avenger'),
            new ImportFleetShip('Mercury Star Runner'),
            new ImportFleetShip('Mercury Star Runner'),
        ], onlyMissing: true);

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(2, $fleet->getShips());
        static::assertSame(2, $fleet->getShipByModel('Avenger')->getQuantity());
        static::assertSame(2, $fleet->getShipByModel('Mercury Star Runner')->getQuantity());
    }
}
