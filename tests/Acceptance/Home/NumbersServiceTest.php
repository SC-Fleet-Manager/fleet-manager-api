<?php

namespace App\Tests\Acceptance\Home;

use App\Application\Home\NumbersService;
use App\Application\Home\Output\NumbersOutput;
use App\Application\Repository\FleetRepositoryInterface;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\OrganizationShipId;
use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Entity\OrganizationFleet;
use App\Entity\User;
use App\Infrastructure\Repository\Fleet\InMemoryFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;

class NumbersServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_stats_numbers(): void
    {
        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $userRepository->setUsers([
            new User(UserId::fromString('00000000-0000-0000-0000-000000000001'), 'Ioni', null, new \DateTimeImmutable()),
            new User(UserId::fromString('00000000-0000-0000-0000-000000000002'), 'Ashuvidz', null, new \DateTimeImmutable()),
            new User(UserId::fromString('00000000-0000-0000-0000-000000000003'), 'Lunia', null, new \DateTimeImmutable()),
        ]);

        /** @var InMemoryOrganizationFleetRepository $organizationFleetRepository */
        $organizationFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $organizationFleetRepository->setOrganizationFleets([
            new OrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000010'), new \DateTimeImmutable()),
            new OrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000011'), new \DateTimeImmutable()),
        ]);

        /** @var InMemoryFleetRepository $fleetRepository */
        $fleetRepository = static::$container->get(FleetRepositoryInterface::class);
        $fleetRepository->setFleets([
            $fleet1 = new Fleet(UserId::fromString('00000000-0000-0000-0000-000000000001'), new \DateTimeImmutable()),
            $fleet2 = new Fleet(UserId::fromString('00000000-0000-0000-0000-000000000002'), new \DateTimeImmutable()),
        ]);
        $fleet1->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000020'), 'Avenger', null, 2, new \DateTimeImmutable());
        $fleet1->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000021'), 'Mercury', null, 3, new \DateTimeImmutable());
        $fleet2->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000022'), 'Javelin', null, 1, new \DateTimeImmutable());

        /** @var NumbersService $service */
        $service = static::$container->get(NumbersService::class);
        $output = $service->handle();
        static::assertEquals(new NumbersOutput(users: 3, fleets: 2, ships: 6), $output);
    }

    /**
     * @test
     */
    public function it_should_return_zero_if_no_data(): void
    {
        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $userRepository->setUsers([]);

        /** @var NumbersService $service */
        $service = static::$container->get(NumbersService::class);
        $output = $service->handle();
        static::assertEquals(new NumbersOutput(0, 0, 0), $output);
    }
}
