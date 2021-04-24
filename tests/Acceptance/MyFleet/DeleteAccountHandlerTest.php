<?php

namespace App\Tests\Acceptance\MyFleet;

use App\Application\MyFleet\DeleteAccountHandler;
use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Event\DeletedUserEvent;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Tests\Acceptance\KernelTestCase;

class DeleteAccountHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_delete_the_fleet_and_ships_of_deleted_user(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');
        $fleet = new Fleet($userId, new \DateTimeImmutable('2021-01-01T12:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000010'), 'Avenger', null, 2, new \DateTimeImmutable('2021-01-01T13:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000011'), 'Mercury Star Runner', 'https://example.com/mercury.jpg', 10, new \DateTimeImmutable('2021-01-01T14:00:00+02:00'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000012'), 'Javelin', 'https://example.com/javelin.jpg', 1, new \DateTimeImmutable('2021-01-01T15:00:00+02:00'));

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([$fleet]);

        static::$container->get(DeleteAccountHandler::class)(new DeletedUserEvent($userId, 'Ioni'));

        $fleet = $fleetRepository->getFleetByUser($userId);
        static::assertNull($fleet, 'The fleet should be deleted.');
    }
}
