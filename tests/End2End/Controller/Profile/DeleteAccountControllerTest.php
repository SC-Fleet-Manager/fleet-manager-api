<?php

namespace App\Tests\End2End\Controller\Profile;

use App\Tests\End2End\WebTestCase;

class DeleteAccountControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_delete_account_and_all_data_of_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', false, 5, '2021-03-20T15:50:00+01:00', '2021-03-21T15:50:00+01:00');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, model, quantity)
                VALUES ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000001', 'Avenger', 2);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/profile/delete-account', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer ' . static::generateToken('Ioni'),
        ]);

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM users WHERE id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertFalse($result, 'The user should be deleted in DB.');
        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM fleets WHERE user_id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertFalse($result, 'The fleet should be deleted in DB.');
        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM ships WHERE fleet_id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertFalse($result, 'The ships should be deleted in DB.');
    }
}
