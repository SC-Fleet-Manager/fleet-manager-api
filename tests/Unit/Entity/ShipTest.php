<?php

namespace App\Tests\Unit\Entity;

use App\Domain\ShipId;
use App\Domain\UserId;
use App\Entity\Fleet;
use App\Entity\Ship;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\InvalidArgumentException;

class ShipTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_create_regular_ship(): void
    {
        $ship = new Ship(ShipId::fromString('00000000000000000000000001'), static::createFleet(), 'Avenger', null, 1);
        static::assertSame('00000000000000000000000001', (string) $ship->getId());
        static::assertSame('Avenger', $ship->getName());
        static::assertNull($ship->getImageUrl());
        static::assertSame(1, $ship->getQuantity());
    }

    /**
     * @test
     */
    public function it_should_not_create_with_too_short_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Ship(ShipId::fromString('00000000000000000000000001'), static::createFleet(), 'A', null, 1);
    }

    /**
     * @test
     */
    public function it_should_not_create_with_too_long_name(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Ship(ShipId::fromString('00000000000000000000000001'), static::createFleet(), str_repeat('A', 33), null, 1);
    }

    /**
     * @test
     */
    public function it_should_not_create_with_zero_quantity(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Ship(ShipId::fromString('00000000000000000000000001'), static::createFleet(), 'Avenger', null, 0);
    }

    /**
     * @test
     */
    public function it_should_not_create_with_bad_image_url(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Ship(ShipId::fromString('00000000000000000000000001'), static::createFleet(), 'Avenger', 'bad://example.com', 1);
    }

    /**
     * @test
     */
    public function it_should_create_with_image_url(): void
    {
        $ship = new Ship(ShipId::fromString('00000000000000000000000001'), static::createFleet(), 'Avenger', 'https://example.com/picture.jpg', 1);
        static::assertSame('https://example.com/picture.jpg', $ship->getImageUrl());
    }

    private static function createFleet(): Fleet
    {
        return new Fleet(UserId::fromString('00000000000000000000000020'), new \DateTimeImmutable('2021-01-01T10:00:00Z'));
    }
}
