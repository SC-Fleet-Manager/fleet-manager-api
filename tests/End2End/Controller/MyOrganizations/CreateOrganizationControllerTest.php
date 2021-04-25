<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class CreateOrganizationControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_create_an_orga_for_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, handle, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', 'ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, model, image_url, quantity)
                VALUES ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000001', 'Avenger', null, 2),
                       ('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000001', 'Mercury Star Runner', 'https://example.com/mercury.jpg', 10);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/organizations/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'name' => 'Force Coloniale Unifiée',
            'sid' => 'fcu',
            'logoUrl' => 'https://robertsspaceindustries.com/avenger.jpg',
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organizations WHERE sid = 'FCU';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'The orga should be created.');
        static::assertArraySubset([
            'name' => 'Force Coloniale Unifiée',
            'logo_url' => 'https://robertsspaceindustries.com/avenger.jpg',
        ], $result);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT os.*, osm.quantity as member_quantity FROM organization_ships os LEFT JOIN organization_ship_members osm ON osm.organization_ship_id = os.id
                WHERE model = 'Avenger' and member_id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertSame(2, $result['quantity']);
        static::assertSame(2, $result['member_quantity']);
    }

    /**
     * @test
     */
    public function it_should_error_if_create_an_orga_with_existing_sid(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Some user', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU', '2021-01-02T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/organizations/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'name' => 'Force Coloniale Unifiée',
            'sid' => 'FCU',
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('sid', $json['violations']['violations'][0]['propertyPath']);
        static::assertSame('This SID is already taken.', $json['violations']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_error_if_logged_user_creates_its_10th_orga(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU1', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU2', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000012', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU3', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000013', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU4', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000014', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU5', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000015', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU6', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000016', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU7', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000017', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU8', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000018', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU9', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000019', '00000000-0000-0000-0000-000000000001', 'An orga', 'FCU10', '2021-01-02T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/organizations/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'name' => 'Force Coloniale Unifiée',
            'sid' => 'fcu',
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('', $json['violations']['violations'][0]['propertyPath']);
        static::assertSame('You have reached the limit of 10 organizations created.', $json['violations']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('POST', '/api/organizations/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
