<?php

namespace App\Tests\End2End\Controller\Profile;

use App\Tests\End2End\WebTestCase;

class ProfileControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_infos_of_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, nickname, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', 'Ioni_custom_nickname', false, 5, '2021-03-20T15:50:00+01:00', '2021-03-21T15:50:00+01:00');
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame([
            'id' => '00000000-0000-0000-0000-000000000001',
            'auth0Username' => 'Ioni',
            'nickname' => 'Ioni_custom_nickname',
            'supporterVisible' => false,
            'coins' => 5,
            'createdAt' => '2021-03-20T14:50:00+00:00',
        ], $json);
    }
}
