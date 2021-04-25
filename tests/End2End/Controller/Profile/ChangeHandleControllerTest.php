<?php

namespace App\Tests\End2End\Controller\Profile;

use App\Tests\End2End\WebTestCase;

class ChangeHandleControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_change_the_handle_of_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, nickname, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', null, true, 0, '2021-03-20T15:50:00+01:00', null);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/profile/change-handle', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'handle' => 'MY_handle',
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT handle FROM users WHERE id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertSame('my_handle', $result['handle']);
    }

    /**
     * @test
     */
    public function it_should_error_if_handle_already_taken(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, nickname, handle, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', null, null, true, 0, '2021-03-20T15:50:00+01:00', null),
                       ('00000000-0000-0000-0000-000000000002', '["ROLE_USER"]', 'A user', null, 'my_handle', true, 0, '2021-03-21T15:50:00+01:00', null);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/profile/change-handle', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'handle' => ' MY_handle ',
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('handle', $json['violations']['violations'][0]['propertyPath']);
        static::assertSame('This handle is already taken.', $json['violations']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_not_error_if_handle_not_changed(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, nickname, handle, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', null, 'my_handle', true, 0, '2021-03-20T15:50:00+01:00', null);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/profile/change-handle', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'handle' => 'MY_handle',
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());
    }
}
