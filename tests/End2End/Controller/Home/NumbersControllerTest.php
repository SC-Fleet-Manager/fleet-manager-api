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
        static::$connection->executeQuery(<<<SQL
                INSERT INTO users(id, roles, auth0_username, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000000', '["ROLE_USER"]', 'Ioni', true, 0, '2021-03-20T15:50:00Z', '2021-03-20T15:50:00Z'),
                       ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ashuvidz', true, 0, '2021-03-20T15:50:00Z', '2021-03-20T15:50:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/numbers', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertArraySubset([
            'users' => 2,
        ], $json);
    }
}
