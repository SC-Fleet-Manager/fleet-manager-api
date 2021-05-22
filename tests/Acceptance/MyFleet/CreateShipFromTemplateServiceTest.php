<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\MyFleet\CreateShipFromTemplateService;
use App\Application\Provider\ListTemplatesProviderInterface;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Event\UpdatedFleetEvent;
use App\Domain\Event\UpdatedShip;
use App\Domain\MyFleet\UserShipTemplate;
use App\Domain\ShipId;
use App\Domain\ShipTemplateId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Provider\MyFleet\InMemoryListTemplatesProvider;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class CreateShipFromTemplateServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_create_a_ship_based_on_ship_template(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $shipId = ShipId::fromString('00000000-0000-0000-0000-000000000010');
        $templateId = ShipTemplateId::fromString('00000000-0000-0000-0000-000000000020');

        /** @var InMemoryListTemplatesProvider $listTemplatesProvider */
        $listTemplatesProvider = static::$container->get(ListTemplatesProviderInterface::class);
        $listTemplatesProvider->setShipTemplateOfUser(
            new UserShipTemplate($templateId, 'Avenger Titan', 'https://example.com/avenger.jpg'),
        );

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([
            new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00')),
        ]);

        /** @var CreateShipFromTemplateService $service */
        $service = static::$container->get(CreateShipFromTemplateService::class);
        $service->handle($userId, $shipId, $templateId);

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(1, $fleet->getShips());
        static::assertEquals($shipId, $fleet->getShips()[(string) $shipId]->getId());
        static::assertSame('Avenger Titan', $fleet->getShips()[(string) $shipId]->getModel());
        static::assertSame('https://example.com/avenger.jpg', $fleet->getShips()[(string) $shipId]->getImageUrl());
        static::assertSame(1, $fleet->getShips()[(string) $shipId]->getQuantity());

        /** @var InMemoryTransport $transport */
        $transport = static::$container->get('messenger.transport.organizations_sub');
        static::assertCount(1, $transport->getSent());
        /** @var UpdatedFleetEvent $message */
        $message = $transport->getSent()[0]->getMessage();
        static::assertInstanceOf(UpdatedFleetEvent::class, $message);
        static::assertEquals(new UpdatedFleetEvent(
            $userId,
            [
                new UpdatedShip('Avenger Titan', 'https://example.com/avenger.jpg', 1),
            ],
            1,
        ), $message);
    }
}
