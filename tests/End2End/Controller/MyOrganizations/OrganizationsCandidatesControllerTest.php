<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class OrganizationsCandidatesControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_list_of_candidates_of_an_orga_for_logged_founder(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, nickname, handle, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', 'Ioni', 'ioni', '2021-01-01T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000002', '["ROLE_USER"]', 'User 1', null, 'user_1', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000003', '["ROLE_USER"]', 'User 2', 'User 2', null, '2021-01-03T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, logo_url, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga 1', 'FCU1', 'https://example.org/logo.png', '2021-01-01T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true),
                       ('00000000-0000-0000-0000-000000000002', '00000000-0000-0000-0000-000000000010', false),
                       ('00000000-0000-0000-0000-000000000003', '00000000-0000-0000-0000-000000000010', false);
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/candidates', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame([
            'candidates' => [
                [
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'nickname' => null,
                    'handle' => 'user_1',
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000003',
                    'nickname' => 'User 2',
                    'handle' => null,
                ],
            ],
        ], $json);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('GET', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/candidates', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
