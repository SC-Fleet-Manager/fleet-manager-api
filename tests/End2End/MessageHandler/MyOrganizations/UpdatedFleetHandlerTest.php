<?php

namespace App\Tests\End2End\MessageHandler\MyOrganizations;

use App\Tests\End2End\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UpdatedFleetHandlerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_update_the_fleet_to_all_orgas_of_owner(): void
    {
        $memberId = '00000000-0000-0000-0000-000000000001';
        $otherMemberId = '00000000-0000-0000-0000-000000000002';

        static::$connection->executeStatement(<<<SQL
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '$memberId', 'Founder', 'FOUNDER', '2021-01-01T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '$otherMemberId', 'Not joined', 'NOT', '2021-01-02T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('$memberId',      '00000000-0000-0000-0000-000000000010', true),
                       ('$otherMemberId', '00000000-0000-0000-0000-000000000011', true),
                       ('$memberId',      '00000000-0000-0000-0000-000000000011', false);
                INSERT INTO organization_fleets(orga_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '2021-01-01T11:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '2021-01-02T11:00:00Z');
                INSERT INTO organization_ships(id, organization_fleet_id, model, image_url)
                VALUES ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000010', 'Avenger Titan-XL', 'https://example.org/avenger.jpg');
                INSERT INTO organization_ship_members(member_id, organization_ship_id, quantity)
                VALUES ('$otherMemberId', '00000000-0000-0000-0000-000000000020', 3);
                INSERT INTO organization_fleet_member_versions(member_id, organization_fleet_id, version)
                VALUES ('$memberId', '00000000-0000-0000-0000-000000000010', 2);
                INSERT INTO messenger_messages(body, headers, queue_name, created_at, available_at, delivered_at)
                VALUES (
                        '{"ownerId":"$memberId","ships":[{"model":"-avêngËr,tÏtan-xl!","logoUrl":"https:\/\/starcitizen.tools\/new_avenger.jpg","quantity":2},{"model":"Gladius","logoUrl":null,"quantity":3}],"version":3}',
                        '{"type":"App\\\\Domain\\\\Event\\\\UpdatedFleetEvent","X-Message-Stamp-Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp":"[{\"busName\":\"event.bus\"}]","Content-Type":"application\/json"}',
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
        )->fetchAllAssociative();
        static::assertEmpty($result, 'Message should be consumed.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT os.*, osm.quantity as member_quantity FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000010' and model = 'Avenger Titan-XL' and member_id = '$memberId';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'Should create the ship for this member.');
        static::assertSame(5, $result['quantity']);
        static::assertSame(2, $result['member_quantity']);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000011' and model = 'Gladius';
            SQL
        )->fetchAssociative();
        static::assertFalse($result, 'Should not be updated, user is not member of this orga.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000010' and model = 'Gladius' and member_id = '$memberId';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'Should create the new ship.');
        static::assertSame(3, $result['quantity']);
        static::assertNull($result['image_url']);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT os.quantity as total_quantity, osm.* FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000010' and organization_ship_id = '00000000-0000-0000-0000-000000000020' and member_id = '$memberId';
            SQL
        )->fetchAssociative();
        static::assertSame(5, $result['total_quantity']);
        static::assertSame(2, $result['quantity']);
    }
}
