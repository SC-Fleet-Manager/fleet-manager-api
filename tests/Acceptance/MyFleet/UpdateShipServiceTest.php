<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\MyFleet\UpdateShipService;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Event\UpdatedFleetShipEvent;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class UpdateShipServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_update_a_ship_of_logged_user(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $shipId = ShipId::fromString('00000000-0000-0000-0000-000000000010');

        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip($shipId, 'Avenger', null, 2, new \DateTimeImmutable('2021-01-01T10:00:00Z'));
        $fleet->getAndClearEvents();

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var UpdateShipService $service */
        $service = static::$container->get(UpdateShipService::class);
        $service->handle($userId, $shipId, 'Avenger 2', 'https://example.com/picture.jpg', 4);

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(1, $fleet->getShips());
        static::assertEquals($shipId, $fleet->getShips()[(string) $shipId]->getId());
        static::assertSame('Avenger 2', $fleet->getShips()[(string) $shipId]->getModel());
        static::assertSame('https://example.com/picture.jpg', $fleet->getShips()[(string) $shipId]->getImageUrl());
        static::assertSame(4, $fleet->getShips()[(string) $shipId]->getQuantity());

        /** @var InMemoryTransport $transport */
        $transport = static::$container->get('messenger.transport.organizations_sub');
        static::assertCount(1, $transport->getSent());
        /** @var UpdatedFleetShipEvent $message */
        $message = $transport->getSent()[0]->getMessage();
        static::assertInstanceOf(UpdatedFleetShipEvent::class, $message);
        static::assertEquals(new UpdatedFleetShipEvent(
            $userId,
            'Avenger 2',
            'https://example.com/picture.jpg',
            4,
        ), $message);
    }
}
