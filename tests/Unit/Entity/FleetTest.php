<?php

namespace App\Tests\Unit\Entity;

use App\Domain\FleetId;
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
        $fleet = new Fleet(FleetId::fromString('00000000000000000000000001'), UserId::fromString('00000000000000000000000010'), new \DateTimeImmutable('2021-01-01T10:00:00Z'));
        static::assertSame('00000000000000000000000001', (string) $fleet->getId());
        static::assertSame('00000000000000000000000010', (string) $fleet->getUserId());
        static::assertEquals(new \DateTimeImmutable('2021-01-01T10:00:00Z'), $fleet->getUpdatedAt());
    }
}
