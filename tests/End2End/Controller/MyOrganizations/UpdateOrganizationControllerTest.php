<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class UpdateOrganizationControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_update_an_orga(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga', 'ORG', '2021-01-01T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/organizations/00000000-0000-0000-0000-000000000010/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'name' => 'Force Coloniale Unifiée',
            'logoUrl' => 'https://robertsspaceindustries.com/fcu.png',
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM organizations WHERE sid = 'ORG';
            SQL
        )->fetchAssociative();
        static::assertArraySubset([
            'name' => 'Force Coloniale Unifiée',
            'logo_url' => 'https://robertsspaceindustries.com/fcu.png',
        ], $result);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('POST', '/api/organizations/00000000-0000-0000-0000-000000000010/update', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
