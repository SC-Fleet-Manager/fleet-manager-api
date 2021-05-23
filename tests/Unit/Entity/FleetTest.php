<?php

namespace App\Tests\Unit\Entity;

use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use PHPUnit\Framework\TestCase;

class FleetTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_create_regular_fleet(): void
    {
        $fleet = new Fleet(UserId::fromString('00000000-0000-0000-0000-000000000010'), new \DateTimeImmutable('2021-01-01T10:00:00Z'));
        static::assertSame('00000000-0000-0000-0000-000000000010', (string) $fleet->getUserId());
        static::assertEquals(new \DateTimeImmutable('2021-01-01T10:00:00Z'), $fleet->getUpdatedAt());
    }

    /**
     * @test
     * @dataProvider it_should_return_same_name_ship_provide
     */
    public function it_should_return_same_name_ship(string $name): void
    {
        $fleet = new Fleet(UserId::fromString('00000000-0000-0000-0000-000000000010'), new \DateTimeImmutable('2021-01-01T10:00:00Z'));
        $fleet->addShip(ShipId::fromString('00000000-0000-0000-0000-000000000001'), 'Avenger', null, 1, new \DateTimeImmutable('2021-01-02T10:00:00Z'));

        static::assertNotEmpty($fleet->getShipsByModel($name));
    }

    public function it_should_return_same_name_ship_provide(): iterable
    {
        yield ['Avenger'];
        yield ['ävênger'];
        yield [' -Av,engêr '];
    }
}
