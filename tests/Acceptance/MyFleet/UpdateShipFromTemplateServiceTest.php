<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\MyFleet\UpdateShipFromTemplateService;
use App\Application\Provider\ListTemplatesProviderInterface;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Event\UpdatedFleetEvent;
use App\Domain\Event\UpdatedShip;
use App\Domain\MyFleet\UserShipTemplate;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Domain\ShipId;
use App\Domain\ShipTemplateId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Provider\MyFleet\InMemoryListTemplatesProvider;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Infrastructure\Service\InMemoryEntityIdGenerator;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class UpdateShipFromTemplateServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_update_a_ship_based_on_ship_template(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $shipId = ShipId::fromString('00000000-0000-0000-0000-000000000010');
        $templateId = ShipTemplateId::fromString('00000000-0000-0000-0000-000000000020');
        $oldTemplateId = ShipTemplateId::fromString('00000000-0000-0000-0000-000000000021');

        /** @var InMemoryListTemplatesProvider $listTemplatesProvider */
        $listTemplatesProvider = static::$container->get(ListTemplatesProviderInterface::class);
        $listTemplatesProvider->setShipTemplateOfUser(
            new UserShipTemplate($templateId, 'Gladius', 'https://example.com/gladius.jpg'),
        );

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([
            $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00')),
        ]);
        /** @var InMemoryEntityIdGenerator $entityIdGenerator */
        $entityIdGenerator = static::$container->get(EntityIdGeneratorInterface::class);
        $entityIdGenerator->setUid($shipId);
        $fleet->addShipFromTemplate(
            new UserShipTemplate($oldTemplateId, 'Avenger Titan', 'https://example.org/avenger.jpg'),
            2,
            new \DateTimeImmutable('2021-01-02 10:00:00'),
            $entityIdGenerator,
        );

        $fleet->getAndClearEvents();

        /** @var UpdateShipFromTemplateService $service */
        $service = static::$container->get(UpdateShipFromTemplateService::class);
        $service->handle($userId, $shipId, $templateId, 3);

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertCount(1, $fleet->getShips());
        static::assertSame('Gladius', $fleet->getShips()[(string) $shipId]->getModel());
        static::assertSame('https://example.com/gladius.jpg', $fleet->getShips()[(string) $shipId]->getImageUrl());
        static::assertSame(3, $fleet->getShips()[(string) $shipId]->getQuantity());
        static::assertEquals($templateId, $fleet->getShips()[(string) $shipId]->getTemplateId());

        /** @var InMemoryTransport $transport */
        $transport = static::$container->get('messenger.transport.organizations_sub');
        static::assertCount(1, $transport->getSent());
        /** @var UpdatedFleetEvent $message */
        $message = $transport->getSent()[0]->getMessage();
        static::assertInstanceOf(UpdatedFleetEvent::class, $message);
        static::assertEquals(new UpdatedFleetEvent(
            $userId,
            [
                new UpdatedShip('Gladius', 'https://example.com/gladius.jpg', 3),
            ],
            1,
        ), $message);
    }
}
