<?php

namespace App\Tests\End2End\Controller\Profile;

use App\Tests\End2End\WebTestCase;

class ChangeNicknameControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_change_nickname_of_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, nickname, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', null, true, 0, '2021-03-20T15:50:00+01:00', '2021-03-21T15:50:00+01:00');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/profile/change-nickname', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'nickname' => 'my_nickname',
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT nickname FROM users WHERE id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertSame('my_nickname', $result['nickname']);
    }

    /**
     * @test
     */
    public function it_should_error_if_new_nickname_is_less_than_2_chars(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, nickname, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', 'should_not_change', true, 0, '2021-03-20T15:50:00+01:00', '2021-03-21T15:50:00+01:00');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/profile/change-nickname', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'nickname' => 'A',
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame('invalid_form', $json['error']);
        static::assertSame('nickname', $json['violations']['violations'][0]['propertyPath']);
        static::assertSame('The nickname must have 2 characters or more.', $json['violations']['violations'][0]['title']);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT nickname FROM users WHERE id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertSame('should_not_change', $result['nickname']);
    }
}
