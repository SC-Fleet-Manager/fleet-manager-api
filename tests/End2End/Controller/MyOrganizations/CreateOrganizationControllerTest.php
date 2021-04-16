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
    }

    /**
     * @test
     */
    public function it_should_error_if_create_an_orga_with_existing_sid(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Some user', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, normalized_name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU', '2021-01-02T10:00:00Z');
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
                INSERT INTO organizations(id, founder_id, name, normalized_name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU1', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU2', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000012', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU3', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000013', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU4', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000014', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU5', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000015', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU6', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000016', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU7', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000017', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU8', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000018', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU9', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000019', '00000000-0000-0000-0000-000000000001', 'An orga', 'An orga', 'FCU10', '2021-01-02T10:00:00Z');
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
