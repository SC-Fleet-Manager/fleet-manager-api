<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class KickMemberControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_kick_member_off_the_organization_and_delete_its_ships_from_orga(): void
    {
        $founderId = '00000000-0000-0000-0000-000000000001';
        $memberId = '00000000-0000-0000-0000-000000000002';
        $orgaId = '00000000-0000-0000-0000-000000000010';

        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('$founderId', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('$orgaId', '$founderId', 'Orga 1', 'ORG', '2021-01-01T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('$founderId', '$orgaId', true),
                       ('$memberId',  '$orgaId', true);
                INSERT INTO organization_fleets(orga_id, updated_at)
                VALUES ('$orgaId', '2021-01-01T11:00:00Z');
                INSERT INTO organization_ships(id, organization_fleet_id, model, image_url)
                VALUES ('00000000-0000-0000-0000-000000000020', '$orgaId', 'Avenger', 'https://example.org/avenger.jpg');
                INSERT INTO organization_ship_members(member_id, organization_ship_id, quantity)
                VALUES ('$founderId', '00000000-0000-0000-0000-000000000020', 2),
                       ('$memberId',  '00000000-0000-0000-0000-000000000020', 3);
            SQL
        );

        static::xhr('POST', "/api/organizations/manage/$orgaId/kick-member/$memberId", [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT os.*, osm.quantity as member_quantity FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE organization_fleet_id = '$orgaId' and model = 'Avenger' and member_id = '$memberId';
            SQL
        )->fetchAssociative();
        static::assertFalse($result);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships WHERE organization_fleet_id = '$orgaId' and model = 'Avenger';
            SQL
        )->fetchAssociative();
        static::assertSame(2, $result['quantity']);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::xhr('POST', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/kick-member/00000000-0000-0000-0000-000000000001', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = static::json();
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
