<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\UpdatedFleetHandler;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Event\UpdatedFleetEvent;
use App\Domain\Event\UpdatedShip;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Domain\UserId;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Infrastructure\Service\InMemoryEntityIdGenerator;
use App\Tests\Acceptance\KernelTestCase;
use Webmozart\Assert\InvalidArgumentException;

class UpdatedFleetHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_update_the_fleet_to_all_orgas_of_owner(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryOrganizationRepository $orgaRepo */
        $orgaRepo = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepo->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000010'),
            MemberId::fromString((string) $userId),
            'member orga',
            'MEMORG',
            null,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
        ));
        $orgaRepo->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000011'),
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'not member orga',
            'NMO',
            null,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
        ));

        $orgaFleets = [];
        $orgaFleet = new OrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000010'), new \DateTimeImmutable('2021-01-01 10:00:00'));

        /** @var InMemoryEntityIdGenerator $entityIdGenerator */
        $entityIdGenerator = static::$container->get(EntityIdGeneratorInterface::class);
        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');
        $orgaFleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000001'),
            '_AvëngÊr Titan,',
            'https://example.org/avenger.jpg',
            1,
            new \DateTimeImmutable('2021-01-02 10:00:00'),
            $entityIdGenerator,
        );
        $orgaFleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'Avenger Titan',
            null,
            2,
            new \DateTimeImmutable('2021-01-02 10:00:00'),
            $entityIdGenerator,
        );
        $orgaFleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000001'),
            'Aurora MR',
            null,
            1,
            new \DateTimeImmutable('2021-01-03 10:00:00'),
            $entityIdGenerator,
        );
        $orgaFleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'Gladius',
            null,
            1,
            new \DateTimeImmutable('2021-01-04 10:00:00'),
            $entityIdGenerator,
        );
        $orgaFleets[] = $orgaFleet;
        $orgaFleet = new OrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000011'), new \DateTimeImmutable('2021-01-01 10:00:00'));

        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');
        $orgaFleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'Avenger Titan',
            null,
            1,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
            $entityIdGenerator,
        );
        $orgaFleets[] = $orgaFleet;

        /** @var InMemoryOrganizationFleetRepository $orgaFleetRepo */
        $orgaFleetRepo = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $orgaFleetRepo->setOrganizationFleets($orgaFleets);

        /** @var UpdatedFleetHandler $handler */
        $handler = static::$container->get(UpdatedFleetHandler::class);
        ($handler)(new UpdatedFleetEvent(
            $userId,
            [
                new UpdatedShip('-AvÊnger Titan_', null, 2),
                new UpdatedShip('Javelin', 'https://example.org/javelin.jpg', 3),
            ],
            2,
        ));

        $orgaFleet = $orgaFleetRepo->getOrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000010'));
        static::assertCount(3, $orgaFleet->getShips());
        static::assertSame(4, $orgaFleet->getShipByModel('Avenger Titan')->getQuantity());
        static::assertSame(1, $orgaFleet->getShipByModel('Gladius')->getQuantity());
        static::assertSame(3, $orgaFleet->getShipByModel('Javelin')->getQuantity());
        static::assertNull($orgaFleet->getShipByModel('Aurora MR'));
        $orgaFleet = $orgaFleetRepo->getOrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000011'));
        static::assertCount(1, $orgaFleet->getShips());
        static::assertSame(1, $orgaFleet->getShipByModel('Avenger Titan')->getQuantity());
    }

    /**
     * @test
     */
    public function it_should_do_nothing_if_event_is_too_old_version(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryOrganizationRepository $orgaRepo */
        $orgaRepo = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepo->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000010'),
            MemberId::fromString((string) $userId),
            'My orga',
            'MYORG',
            null,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
        ));

        /** @var InMemoryOrganizationFleetRepository $orgaFleetRepo */
        $orgaFleetRepo = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $orgaFleetRepo->setOrganizationFleets([
            $orgaFleet = new OrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000010'), new \DateTimeImmutable('2021-01-01 10:00:00')),
        ]);
        $orgaFleet->updateMemberFleet(MemberId::fromString((string) $userId), [], 1, new \DateTimeImmutable('2021-01-01 10:00:00'), static::$container->get(EntityIdGeneratorInterface::class));

        /** @var UpdatedFleetHandler $handler */
        $handler = static::$container->get(UpdatedFleetHandler::class);
        ($handler)(new UpdatedFleetEvent(
            $userId,
            [
                new UpdatedShip('Avenger Titan', null, 1),
            ],
            1, // version already handled : error
        ));

        $orgaFleet = $orgaFleetRepo->getOrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000010'));
        static::assertNull($orgaFleet->getShipByModel('Avenger Titan'), 'Old fleet versions should be discarded.');
    }

    /**
     * @test
     */
    public function it_should_error_with_too_long_ship_name(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryOrganizationRepository $orgaRepo */
        $orgaRepo = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepo->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000010'),
            MemberId::fromString((string) $userId),
            'My orga',
            'MYORG',
            null,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
        ));

        $this->expectException(InvalidArgumentException::class);

        /** @var UpdatedFleetHandler $handler */
        $handler = static::$container->get(UpdatedFleetHandler::class);
        ($handler)(new UpdatedFleetEvent(
            $userId,
            [
                new UpdatedShip(str_repeat('A', 61), null, 1),
            ],
            1,
        ));
    }
}
