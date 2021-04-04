<?php

namespace App\Tests\Integration\Repository\FleetRepository;

use App\Tests\Integration\KernelTestCase;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class SaveShipTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_error_on_duplicate_ship_name_for_a_same_fleet(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO fleets(user_id, updated_at) VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-01 10:00:00Z');
                INSERT INTO ships(id, fleet_id, name)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'Avenger'),
                       ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000001', 'Mercury');
            SQL
        );

        $this->expectException(UniqueConstraintViolationException::class);
        static::$connection->executeStatement(<<<SQL
                INSERT INTO ships(id, fleet_id, name)
                VALUES ('00000000-0000-0000-0000-000000000012', '00000000-0000-0000-0000-000000000001', ' -Avênger,');
            SQL
        );
    }
}
