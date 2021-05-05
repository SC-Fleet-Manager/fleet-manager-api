<?php

namespace App\Tests\End2End\MessageHandler\MyOrganizations;

use App\Tests\End2End\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpdatedFleetShipHandlerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_add_or_update_the_ship_to_all_orga_fleets_of_owner(): void
    {
        $memberId = '00000000-0000-0000-0000-000000000001';
        $otherMemberId = '00000000-0000-0000-0000-000000000002';

        static::$connection->executeStatement(<<<SQL
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '$memberId', 'Founder', 'FOUNDER', '2021-01-01T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '$otherMemberId', 'Not joined', 'NOT', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000012', '$otherMemberId', 'Joined', 'JOINED', '2021-01-03T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000013', '$memberId', 'Joined without fleet', 'WOFLEET', '2021-01-04T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('$memberId',      '00000000-0000-0000-0000-000000000010', true),
                       ('$otherMemberId', '00000000-0000-0000-0000-000000000011', true),
                       ('$memberId',      '00000000-0000-0000-0000-000000000011', false),
                       ('$otherMemberId', '00000000-0000-0000-0000-000000000012', true),
                       ('$memberId',      '00000000-0000-0000-0000-000000000012', true),
                       ('$memberId',      '00000000-0000-0000-0000-000000000013', true);
                INSERT INTO organization_fleets(orga_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '2021-01-01T11:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '2021-01-02T11:00:00Z'),
                       ('00000000-0000-0000-0000-000000000012', '2021-01-03T11:00:00Z');
                INSERT INTO organization_ships(id, organization_fleet_id, model, image_url)
                VALUES ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000010', 'Avenger Titan-XL', 'https://example.org/avenger.jpg'),
                       ('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000012', 'Mercury Star Runner', null);
                INSERT INTO organization_ship_members(member_id, organization_ship_id, quantity)
                VALUES ('$memberId',      '00000000-0000-0000-0000-000000000021', 1),
                       ('$otherMemberId', '00000000-0000-0000-0000-000000000020', 3),
                       ('$otherMemberId', '00000000-0000-0000-0000-000000000021', 1);
                INSERT INTO messenger_messages(body, headers, queue_name, created_at, available_at, delivered_at)
                VALUES (
                        '{"ownerId":"$memberId","model":"-avêngËr,tÏtan-xl!","logoUrl":"https:\/\/starcitizen.tools\/new_avenger.jpg","quantity":2}',
                        '{"type":"App\\\\Domain\\\\Event\\\\UpdatedFleetShipEvent","X-Message-Stamp-Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp":"[{\"busName\":\"event.bus\"}]","Content-Type":"application\/json"}',
                        'organizations_events',
                        '2021-01-01T10:00:00Z',
                        '2021-01-01T11:00:00Z',
                        null
                        );
            SQL
        );

        $app = new Application(static::$kernel);
        $command = $app->find('messenger:consume');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'receivers' => ['organizations_sub'],
            '--limit' => 1,
            '--time-limit' => 3,
        ]);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM messenger_messages;
            SQL
        )->fetchAll();
        static::assertEmpty($result, 'Message should be consumed.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT os.*, osm.quantity as member_quantity FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000010' and model = 'Avenger Titan-XL' and member_id = '$memberId';
            SQL
        )->fetchAssociative();
        static::assertSame(5, $result['quantity']);
        static::assertSame(2, $result['member_quantity']);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_fleets WHERE orga_id = '00000000-0000-0000-0000-000000000013';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'Orga fleet should be created.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000011' and model = 'Avenger Titan-XL';
            SQL
        )->fetchAssociative();
        static::assertFalse($result, 'Should not be updated, user is not member of this orga.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000012' and model = '-avêngËr,tÏtan-xl!' and member_id = '$memberId';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'Should create the new ship in the orga.');
        static::assertSame(2, $result['quantity']);
        static::assertSame('https://starcitizen.tools/new_avenger.jpg', $result['image_url']);
    }

    /**
     * @test
     */
    public function it_should_add_with_name_60_chars_long(): void
    {
        $memberId = '00000000-0000-0000-0000-000000000001';
        $shipName = str_repeat('a', 60);

        static::$connection->executeStatement(<<<SQL
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '$memberId', 'Founder', 'FOUNDER', '2021-01-01T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('$memberId', '00000000-0000-0000-0000-000000000010', true);
                INSERT INTO organization_fleets(orga_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '2021-01-01T11:00:00Z');
                INSERT INTO messenger_messages(body, headers, queue_name, created_at, available_at, delivered_at)
                VALUES (
                        '{"ownerId":"$memberId","model":"$shipName","logoUrl":null,"quantity":1}',
                        '{"type":"App\\\\Domain\\\\Event\\\\UpdatedFleetShipEvent","X-Message-Stamp-Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp":"[{\"busName\":\"event.bus\"}]","Content-Type":"application\/json"}',
                        'organizations_events',
                        '2021-01-01T10:00:00Z',
                        '2021-01-01T11:00:00Z',
                        null
                        );
            SQL
        );

        $app = new Application(static::$kernel);
        $command = $app->find('messenger:consume');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'receivers' => ['organizations_sub'],
            '--limit' => 1,
            '--time-limit' => 3,
        ]);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000010' and model = '$shipName';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'Ship should be added.');
    }
}
