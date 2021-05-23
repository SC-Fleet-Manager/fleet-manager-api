<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class DisbandOrganizationControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_disband_the_organization_and_delete_its_fleet_and_ships(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga', 'org', '2021-01-01T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true),
                       ('00000000-0000-0000-0000-000000000002', '00000000-0000-0000-0000-000000000010', false);
                INSERT INTO organization_fleets(orga_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '2021-01-02T10:00:00Z');
                INSERT INTO organization_ships(id, organization_fleet_id, model, image_url, quantity)
                VALUES ('00000000-0000-0000-0000-000000000030', '00000000-0000-0000-0000-000000000010', 'Avenger', 'https://example.org/avenger.jpg', 5),
                       ('00000000-0000-0000-0000-000000000031', '00000000-0000-0000-0000-000000000010', 'Mercury Star Runner', null, 2);
                INSERT INTO organization_ship_members(member_id, organization_ship_id, quantity)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000030', 2),
                       ('00000000-0000-0000-0000-000000000002',  '00000000-0000-0000-0000-000000000031', 3);
            SQL
        );

        static::xhr('POST', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/disband', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organizations WHERE id = '00000000-0000-0000-0000-000000000010';
            SQL
        )->fetchAssociative();
        static::assertFalse($result, 'Orga should be disband.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM memberships WHERE organization_id = '00000000-0000-0000-0000-000000000010';
            SQL
        )->fetchAllAssociative();
        static::assertEmpty($result, 'Orga members should be deleted.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000010';
            SQL
        )->fetchAllAssociative();
        static::assertEmpty($result, 'Orga fleet should be deleted.');
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::xhr('POST', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/disband', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = static::json();
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
