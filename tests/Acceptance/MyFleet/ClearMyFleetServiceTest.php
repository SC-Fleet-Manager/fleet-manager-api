<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\MyFleet\ClearMyFleetService;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Event\UpdatedFleetEvent;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class ClearMyFleetServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_clear_my_fleet(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000011'), 'Avenger', null, 1, new \DateTimeImmutable('2021-01-01T13:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000012'), 'Mercury', null, 2, new \DateTimeImmutable('2021-01-02T13:00:00+02:00'));
        $fleet->getAndClearEvents();

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        /** @var ClearMyFleetService $service */
        $service = static::$container->get(ClearMyFleetService::class);
        $service->handle($userId);

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(0, $fleet->getShips());

        /** @var InMemoryTransport $transport */
        $transport = static::$container->get('messenger.transport.organizations_sub');
        static::assertCount(1, $transport->getSent());
        /** @var UpdatedFleetEvent $message */
        $message = $transport->getSent()[0]->getMessage();
        static::assertInstanceOf(UpdatedFleetEvent::class, $message);
        static::assertEquals(new UpdatedFleetEvent(
            $userId,
            [],
            1,
        ), $message);
    }
}
