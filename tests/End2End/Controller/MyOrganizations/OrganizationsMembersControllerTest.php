<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class OrganizationsMembersControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_list_of_joined_members_of_an_orga_for_logged_founder(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, nickname, handle, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', 'Ioni', null, '2021-01-01T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000002', '["ROLE_USER"]', 'User 1', 'User 1', 'user_1', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000003', '["ROLE_USER"]', 'User 2', 'User 2', null, '2021-01-03T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, logo_url, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga 1', 'FCU1', 'https://example.org/logo.png', '2021-01-01T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true),
                       ('00000000-0000-0000-0000-000000000002', '00000000-0000-0000-0000-000000000010', true),
                       ('00000000-0000-0000-0000-000000000003', '00000000-0000-0000-0000-000000000010', false);
            SQL
        );

        static::xhr('GET', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/members', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = static::json();
        usort($json['members'], static function (array $member1, array $member2): int {
            return $member1['id'] <=> $member2['id'];
        });
        static::assertSame([
            'members' => [
                [
                    'id' => '00000000-0000-0000-0000-000000000001',
                    'nickname' => 'Ioni',
                    'handle' => null,
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000002',
                    'nickname' => 'User 1',
                    'handle' => 'user_1',
                ],
            ],
        ], $json);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::xhr('GET', '/api/organizations/manage/00000000-0000-0000-0000-000000000010/members', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = static::json();
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
