<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\MyFleet\DeleteShipService;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Event\DeletedFleetShipEvent;
use App\Domain\Event\UpdatedFleetShipEvent;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class DeleteShipServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_delete_a_ship_of_logged_user(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000011'), 'Avenger', null, 1, new \DateTimeImmutable('2021-01-01T13:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000012'), 'Mercury', null, 1, new \DateTimeImmutable('2021-01-01T13:00:00+02:00'));
        $fleet->getAndClearEvents();

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var DeleteShipService $service */
        $service = static::$container->get(DeleteShipService::class);
        $service->handle($userId, ShipId::fromString('00000000-0000-0000-0000-000000000011'));

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(1, $fleet->getShips());
        static::assertSame('Mercury', $fleet->getShips()['00000000-0000-0000-0000-000000000012']->getModel());

        /** @var InMemoryTransport $transport */
        $transport = static::$container->get('messenger.transport.organizations_sub');
        static::assertCount(1, $transport->getSent());
        /** @var DeletedFleetShipEvent $message */
        $message = $transport->getSent()[0]->getMessage();
        static::assertInstanceOf(DeletedFleetShipEvent::class, $message);
        static::assertEquals(new DeletedFleetShipEvent(
            $userId,
            'Avenger',
        ), $message);
    }

    /**
     * @test
     */
    public function it_should_not_error_when_delete_an_inexistent_ship_of_logged_user(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var DeleteShipService $service */
        $service = static::$container->get(DeleteShipService::class);
        $service->handle($userId, ShipId::fromString('00000000-0000-0000-0000-000000000011'));

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(0, $fleet->getShips());
    }
}
