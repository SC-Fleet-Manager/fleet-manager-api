<?php

namespace App\Tests\End2End\MessageHandler\MyOrganizations;

use App\Tests\End2End\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class DeletedFleetShipHandlerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_delete_the_ship_from_all_orga_fleets_of_owner(): void
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
                       ('00000000-0000-0000-0000-000000000012', '2021-01-03T11:00:00Z'),
                       ('00000000-0000-0000-0000-000000000013', '2021-01-04T11:00:00Z');
                INSERT INTO organization_ships(id, organization_fleet_id, model, image_url)
                VALUES ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000010', 'Avenger Titan-XL', 'https://example.org/avenger.jpg'),
                       ('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000012', 'Mercury Star Runner', null),
                       ('00000000-0000-0000-0000-000000000022', '00000000-0000-0000-0000-000000000013', '-avêngËr,tÏtan-xl!', null);
                INSERT INTO organization_ship_members(member_id, organization_ship_id, quantity)
                VALUES ('$memberId',      '00000000-0000-0000-0000-000000000021', 1),
                       ('$otherMemberId', '00000000-0000-0000-0000-000000000021', 1),
                       ('$memberId',      '00000000-0000-0000-0000-000000000020', 2),
                       ('$otherMemberId', '00000000-0000-0000-0000-000000000020', 3),
                       ('$memberId',      '00000000-0000-0000-0000-000000000022', 5);
                INSERT INTO messenger_messages(body, headers, queue_name, created_at, available_at, delivered_at)
                VALUES (
                        '{"ownerId":"$memberId","model":"-avêngËr,tÏtan-xl!"}',
                        '{"type":"App\\\\Domain\\\\Event\\\\DeletedFleetShipEvent","X-Message-Stamp-Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp":"[{\"busName\":\"event.bus\"}]","Content-Type":"application\/json"}',
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
        static::assertFalse($result);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000010' and model = 'Avenger Titan-XL';
            SQL
        )->fetchAssociative();
        static::assertSame(3, $result['quantity']);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000013' and model = '-avêngËr,tÏtan-xl!';
            SQL
        )->fetchAssociative();
        static::assertFalse($result, 'Orga ship should be deleted because quantity == 0.');
    }
}
