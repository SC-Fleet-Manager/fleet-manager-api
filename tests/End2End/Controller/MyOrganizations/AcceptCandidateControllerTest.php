<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class AcceptCandidateControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_accept_candidate_of_an_organization(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000002', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, model, image_url, quantity)
                VALUES ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000002', 'Avenger', null, 2);
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga', 'org', '2021-01-01T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true),
                       ('00000000-0000-0000-0000-000000000002', '00000000-0000-0000-0000-000000000010', false);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/accept-candidate/00000000-0000-0000-0000-000000000002', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM memberships WHERE organization_id = '00000000-0000-0000-0000-000000000010' AND member_id = '00000000-0000-0000-0000-000000000002';
            SQL
        )->fetchAssociative();
        static::assertTrue($result['joined'], 'Member should be accepted in the orga.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM memberships WHERE organization_id = '00000000-0000-0000-0000-000000000010' AND member_id = '00000000-0000-0000-0000-000000000002';
            SQL
        )->fetchAssociative();
        static::assertTrue($result['joined'], 'Member should be accepted in the orga.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE organization_fleet_id = '00000000-0000-0000-0000-000000000010' and model = 'Avenger' and member_id = '00000000-0000-0000-0000-000000000002';
            SQL
        )->fetchAssociative();
        static::assertSame(2, $result['quantity']);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('POST', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/accept-candidate/00000000-0000-0000-0000-000000000002', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
