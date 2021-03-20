<?php

namespace App\Tests\End2End\Controller\PatchNote;

use App\Tests\End2End\WebTestCase;

class HasNewPatchNoteControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_true_if_the_user_has_a_new_patch_note_to_read(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', false, 5, '2021-03-20T15:50:00+01:00', '2021-03-21T15:50:00+01:00');
                INSERT INTO patch_note(id, title, body, created_at)
                VALUES ('00000000-0000-0000-0000-000000000010', 'Title', 'Body', '2021-03-21T16:50:00+01:00');
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/has-new-patch-note', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame([
            'hasNewPatchNote' => true,
        ], $json);
    }
}
