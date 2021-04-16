<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class MyOrganizationsControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_list_of_orga_of_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, normalized_name, sid, logo_url, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga 1', 'An orga 1', 'FCU1', 'https://example.org/logo.png', '2021-01-01T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000002', 'An orga 2', 'An orga 2', 'FCU2', null, '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000012', '00000000-0000-0000-0000-000000000003', 'An orga 3', 'An orga 3', 'FCU3', null, '2021-01-03T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true),
                       ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000011', false);
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/my-organizations', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame([
            'organizations' => [
                [
                    'id' => '00000000-0000-0000-0000-000000000010',
                    'name' => 'An orga 1',
                    'sid' => 'FCU1',
                    'logoUrl' => 'https://example.org/logo.png',
                    'joined' => true,
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000011',
                    'name' => 'An orga 2',
                    'sid' => 'FCU2',
                    'logoUrl' => null,
                    'joined' => false,
                ],
            ],
        ], $json);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('GET', '/api/my-organizations', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
