<?php

namespace App\Tests\End2End\Controller\Home;

use App\Tests\End2End\WebTestCase;

class NumbersControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_number_of_users(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', true, 0, '2021-03-20T15:50:00Z', '2021-03-20T15:50:00Z'),
                       ('00000000-0000-0000-0000-000000000002', '["ROLE_USER"]', 'Ashuvidz', true, 0, '2021-03-20T15:50:00Z', '2021-03-20T15:50:00Z');
                INSERT INTO organization_fleets(orga_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '2021-01-01T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000011', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000012', '2021-01-03T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-01T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000002', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, model, quantity)
                VALUES ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000001', 'Avenger', 1),
                       ('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000001', 'Mercury', 2),
                       ('00000000-0000-0000-0000-000000000022', '00000000-0000-0000-0000-000000000002', 'Javelin', 3);
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/numbers', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertArraySubset([
            'users' => 2,
            'fleets' => 3,
            'ships' => 6,
        ], $json);
    }
}
