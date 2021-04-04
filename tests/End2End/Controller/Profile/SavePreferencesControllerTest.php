<?php

namespace App\Tests\End2End\Controller\Profile;

use App\Tests\End2End\WebTestCase;

class SavePreferencesControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_save_user_preferences(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', true, 0, '2021-03-20T15:50:00+01:00', '2021-03-21T15:50:00+01:00');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/profile/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'supporterVisible' => false,
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT supporter_visible FROM users WHERE id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertFalse($result['supporter_visible'], 'SupporterVisible must be false.');
    }

    /**
     * @test
     */
    public function it_should_receive_errors_when_send_an_empty_payload(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', true, 0, '2021-03-20T15:50:00+01:00', '2021-03-21T15:50:00+01:00');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/profile/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], '{}');

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = \json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame('invalid_form', $json['error']);
        static::assertCount(1, $json['formErrors']['violations']);
        static::assertSame('supporterVisible', $json['formErrors']['violations'][0]['propertyPath']);
        static::assertSame('You must choose a supporter visibility.', $json['formErrors']['violations'][0]['title']);
    }
}
