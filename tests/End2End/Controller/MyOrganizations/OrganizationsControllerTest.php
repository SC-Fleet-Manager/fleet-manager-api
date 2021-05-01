<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class OrganizationsControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_list_of_orgas_paginated_with_20(): void
    {
        $orgaValues = '';
        for ($i = 0 + 10; $i < 21 + 10; ++$i) { // more than 20
            $orgaValues .= "('00000000-0000-0000-0000-0000000000$i', '00000000-0000-0000-0000-000000000002', 'An orga $i', 'fcu$i', '2021-01-${i}T10:00:00Z'),";
        }
        $orgaValues = rtrim($orgaValues, ',');
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES $orgaValues;
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true),
                       ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000011', false);
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/organizations', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertCount(20, $json['organizations']);
        static::assertSame('/api/organizations?sinceId=00000000-0000-0000-0000-000000000029', $json['nextUrl']);
        static::assertSame([
            'id' => '00000000-0000-0000-0000-000000000010',
            'name' => 'An orga 10',
            'sid' => 'fcu10',
            'logoUrl' => null,
        ], $json['organizations'][0]);
        static::assertSame([
            'id' => '00000000-0000-0000-0000-000000000011',
            'name' => 'An orga 11',
            'sid' => 'fcu11',
            'logoUrl' => null,
        ], $json['organizations'][1]);
    }

    /**
     * @test
     */
    public function it_should_return_last_page_of_orgas_paginated_with_20(): void
    {
        $orgaValues = '';
        for ($i = 0 + 10; $i < 21 + 10; ++$i) { // more than 20
            $orgaValues .= "('00000000-0000-0000-0000-0000000000$i', '00000000-0000-0000-0000-000000000002', 'An orga $i', 'fcu$i', '2021-01-${i}T10:00:00Z'),";
        }
        $orgaValues = rtrim($orgaValues, ',');
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES $orgaValues;
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true),
                       ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000011', false);
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/organizations?sinceId=00000000-0000-0000-0000-000000000029', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertCount(1, $json['organizations']);
        static::assertNull($json['nextUrl']);
        static::assertSame([
            'id' => '00000000-0000-0000-0000-000000000030',
            'name' => 'An orga 30',
            'sid' => 'fcu30',
            'logoUrl' => null,
        ], $json['organizations'][0]);
    }

    /**
     * @test
     */
    public function it_should_return_a_filtered_list_of_orgas_with_search_query(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000002', 'Les bons gÄrdîEnß!', 'LESBONS', '2021-01-01T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000003', 'Les videurs', 'VIDEURS', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000012', '00000000-0000-0000-0000-000000000004', 'Les douteux', 'GARDIENSSDOUTE', '2021-01-03T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/organizations?search=Gardienss', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertCount(2, $json['organizations']);
        static::assertSame([
            'organizations' => [
                [
                    'id' => '00000000-0000-0000-0000-000000000010',
                    'name' => 'Les bons gÄrdîEnß!',
                    'sid' => 'LESBONS',
                    'logoUrl' => null,
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000012',
                    'name' => 'Les douteux',
                    'sid' => 'GARDIENSSDOUTE',
                    'logoUrl' => null,
                ],
            ],
            'nextUrl' => null,
        ], $json);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('GET', '/api/organizations', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
