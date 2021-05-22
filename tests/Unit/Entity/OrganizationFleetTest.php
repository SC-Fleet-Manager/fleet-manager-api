<?php

namespace App\Tests\Unit\Entity;

use App\Domain\Event\UpdatedShip;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\OrganizationFleet;
use App\Infrastructure\Service\InMemoryEntityIdGenerator;
use PHPUnit\Framework\TestCase;

class OrganizationFleetTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_update_member_fleet_with_deleted_updated_and_added(): void
    {
        $entityIdGenerator = new InMemoryEntityIdGenerator();
        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');

        $fleet = new OrganizationFleet(OrgaId::fromString('00000000-0000-0000-0000-000000000001'), new \DateTimeImmutable('2021-01-01 10:00:00'));
        $fleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000030'),
            '_AvëngÊr Titan,',
            'https://example.org/avenger.jpg',
            2,
            new \DateTimeImmutable('2021-01-02 10:00:00'),
            $entityIdGenerator,
        );
        $fleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000031'),
            '_AvëngÊr Titan,',
            null,
            3,
            new \DateTimeImmutable('2021-01-03 10:00:00'),
            $entityIdGenerator,
        );
        $fleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000030'),
            'Aurora MR',
            null,
            1,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
            $entityIdGenerator,
        );
        $fleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000030'),
            'Cyclone',
            null,
            1,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
            $entityIdGenerator,
        );
        $fleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000031'),
            'Cyclone',
            null,
            2,
            new \DateTimeImmutable('2021-01-02 10:00:00'),
            $entityIdGenerator,
        );
        $fleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000031'),
            'Hull',
            null,
            1,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
            $entityIdGenerator,
        );
        $fleet->createOrUpdateShip(
            MemberId::fromString('00000000-0000-0000-0000-000000000031'),
            'Gladius',
            null,
            1,
            new \DateTimeImmutable('2021-01-01 10:00:00'),
            $entityIdGenerator,
        );

        $fleet->updateMemberFleet(
            MemberId::fromString('00000000-0000-0000-0000-000000000030'),
            [
                new UpdatedShip('Avenger Titan', null, 5),
                new UpdatedShip('-AvÊnger Titan_', null, 7),
                new UpdatedShip('Javelin', 'https://example.org/javelin.jpg', 2),
                new UpdatedShip('Javelin', null, 3),
                new UpdatedShip('Gladius', null, 2),
                new UpdatedShip('Gladius', null, 3),
            ],
            2,
            new \DateTimeImmutable('2021-01-04 10:00:00'),
            $entityIdGenerator,
        );

        static::assertNull($fleet->getShipByModel('Aurora MR'), 'Aurora MR should be deleted.'); // deleted : no owners anymore

        $ship = $fleet->getShipByModel('Avenger Titan'); // updated quantity 2 times
        static::assertSame('_AvëngÊr Titan,', $ship->getModel());
        static::assertSame(15, $ship->getQuantity());
        $ship = $fleet->getShipByModel('Cyclone'); // member deleted
        static::assertSame(2, $ship->getQuantity());
        $ship = $fleet->getShipByModel('Javelin'); // added
        static::assertSame(5, $ship->getQuantity());
        static::assertSame('https://example.org/javelin.jpg', $ship->getImageUrl());
        $ship = $fleet->getShipByModel('Gladius'); // member added
        static::assertSame(6, $ship->getQuantity());
        $ship = $fleet->getShipByModel('Hull'); // not touched
        static::assertSame(1, $ship->getQuantity());
    }
}
